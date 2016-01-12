<?php
namespace AppBundle\Controller;

use Imagine\Gd\Imagine;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Helper;
use AppBundle\Wechat;
use AppBundle\Entity;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\Cookie;
#use AppBundle\Weibo;
#use Imagine\Image\Box;
#use Imagine\Image\Point;
#use Imagine\Image\ImageInterface;
#use Symfony\Component\Filesystem\Filesystem;

#use Symfony\Component\Validator\Constraints\Image;

class DefaultController extends Controller
{
  public function getUser()
  {
		$session = $this->get('session');
		if(null != $session->get('user_id')){
			$user = $this->getDoctrine()->getRepository('AppBundle:WechatUser')->find($session->get('user_id'));
		}
		else{
			$user = $this->getDoctrine()->getRepository('AppBundle:WechatUser')->findOneByOpenId($session->get('open_id'));
		}
		return $user;
  }
	/**
	 * @Route("/", name="_index")
	 */
	public function indexAction(Request $request)
	{
		return $this->render('AppBundle:default:index.html.twig');
	}
	/**
	 * @Route("/lottery/{type}", name="_lottery", requirements={"type"="a|b"})
	 */
	public function lotteryAction(Request $request, $type = 'a')
	{
		$result = array(
			'ret' => 1000,
			'msg' => '已抽奖',
		);
		$user = $this->getUser();
		if($type == 'b' && null == $request->get('code')){
			$result = array(
				'ret' => 3000,
				'msg' => '没有code~',
			);
		}
		else{
			$can_lottery = true;
			if($type == 'b'){
				//$curl = Helper\HttpClient::post('http://cssv5.acer.com.cn/acerext/api/validatesn',array('SerialNumber'=>$request->get('code'),'snid'=>$request->get('code')));
				if( $request->get('code') != '123456'){
					$result = array(
						'ret' => 4000,
						'msg' => '无效的code~',
					);
					$can_lottery = false;
				}
			}

			if( $can_lottery == true){
				$em = $this->getDoctrine()->getManager();
				$em->getConnection()->beginTransaction();
				try{
					$timestamp = time();
					$date1 = date('Y-m-d',$timestamp);
					$date2 = date('Y-m-d', strtotime('+1 day', $timestamp));
					$repo = $em->getRepository('AppBundle:LotteryLog');
					$qb = $repo->createQueryBuilder('a');
					$qb->select('COUNT(a)');
					$qb->where('a.user = :user AND a.createTime >= :date1 AND a.createTime < :date2 AND a.type = :type');
					$qb->setParameter(':user', $user);
					$qb->setParameter(':type', $type);
					$qb->setParameter(':date1', new \DateTime($date1), \Doctrine\DBAL\Types\Type::DATETIME);
					$qb->setParameter(':date2', new \DateTime($date2), \Doctrine\DBAL\Types\Type::DATETIME);
					$count = $qb->getQuery()->getSingleScalarResult();
					if( $count < 1){
						$rand1 = rand(0,10);
						$rand2 = rand(0, 10);
						#中奖
						if($rand1 == $rand2){
							$award_type = rand(1,6);
							$result = array(
								'ret' => 0,
								'msg' => '中奖',
							);
							$has_win = true;
						}
						else{
							$result = array(
								'ret' => 2000,
								'msg' => '未中奖',
							);
							$has_win = false;
							$award_type = 0;
						}
						$log = new Entity\LotteryLog;
						$log->setAwardType($award_type);
						$log->setType($type);
				    $log->setUser($user);
				    $log->setHasWin($has_win);
						$log->setCreateTime(new \DateTime('now'));
						$log->setCreateIp($request->getClientIp());
						$em->persist($log);
						$em->flush();
						$result['id'] = $log->getId();
					}
					$em->getConnection()->commit();
				}
				catch (Exception $e) {
					$em->getConnection()->rollback();
					return new Response($e->getMessage());
				}
			}
				
		}
		
		return new Response(json_encode($result));
	}
	/**
	 * @Route("/award/{id}", name="_award")
	 */
	public function awardAction(Request $request, $id = null)
	{
		$log = $this->getDoctrine()->getRepository('AppBundle:LotteryLog')->find($id);
		if( null == $log || $log->getUser() != $this->getUser()){
			return $this->redirect($this->generateUrl('_no_award'));
		}
		return $this->render('AppBundle:default:award.html.twig', array('log'=>$log));
	}
	/**
	 * @Route("/noAward", name="_no_award")
	 */
	public function noAwardAction(Request $request,$type = 1)
	{
		return $this->render('AppBundle:default:no_award_'.$type.'.html.twig');
	}
	/**
	 * @Route("/gifts", name="_gifts")
	 */
	public function giftsAction(Request $request)
	{
		$user = $this->getUser();
		$em = $this->getDoctrine()->getManager();
		$repo = $em->getRepository('AppBundle:LotteryLog');
		$qb = $repo->createQueryBuilder('a');
		$qb->where('a.user = :user AND a.hasWin = 1');
		$qb->setParameter(':user', $user);
		$logs = $qb->getQuery()->getResult();
		if( null == $logs){
			return $this->redirect($this->generateUrl('_no_award'));
		}
		$info = null != $user->getInfo() ? $user->getInfo() : null;
		return $this->render('AppBundle:default:gifts.html.twig',  array('logs'=>$logs,'info'=>$info));
	}
	/**
	 * @Route("/code", name="_code")
	 */
	public function codeAction(Request $request)
	{
		return $this->render('AppBundle:default:code.html.twig');
	}
	/**
	 * @Route("/post", name="_info_post")
	 */
	public function postAction(Request $request)
	{
		$result = array(
			'ret' => 0,
			'msg' => '',
		);
		$user = $this->getUser();
		if(null != $user->getInfo()){
			$result = array(
				'ret' => 1001,
				'msg' => '您已经提交过信息了,请勿重复提交',
			);
		}
		elseif ($request->getMethod() != 'POST') {
			$result = array(
				'ret' => 1002,
				'msg' => '来源不正确',
			);
		}
		else{
			$username = strip_tags($request->get('username'));
			$mobile = strip_tags($request->get('mobile'));
			$address = strip_tags($request->get('address'));
			$em = $this->getDoctrine()->getManager();
			$info = new Entity\Info;
			$info->setUser($user);
			$info->setUsername($username);
			$info->setMobile($mobile);
			$info->setAddress($address);
			$info->setCreateTime(new \DateTime('now'));
			$info->setCreateIp($request->getClientIp());
			$em->persist($info);
			$em->flush();
		}
		return new Response(json_encode($result));
	}

  /**
   * @Route("/callback", name="_callback")
   */
  public function wechatAction(Request $request)
  {
    $session = $request->getSession();
    $code = $request->query->get('code');
    //$state = $request->query->get('state');
    $app_id = $this->container->getParameter('wechat_appid');
    $secret = $this->container->getParameter('wechat_secret');
    $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $app_id . "&secret=" . $secret . "&code=$code&grant_type=authorization_code";
    $data = Helper\HttpClient::get($url);
    $token = json_decode($data);
    //$session->set('open_id', null);
    if ( isset($token->errcode) && $token->errcode != 0) {
        return new Response('something bad !');
    }

    $wechat_token = $token->access_token;
    $wechat_openid = $token->openid;
    $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$wechat_token}&openid={$wechat_openid}";
    $data = Helper\HttpClient::get($url);
    $user_data = json_decode($data);

    $em = $this->getDoctrine()->getManager();
    $em->getConnection()->beginTransaction();
    try{
        $session->set('open_id', $user_data->openid);
        $repo = $em->getRepository('AppBundle:WechatUser');
        $qb = $repo->createQueryBuilder('a');
        $qb->select('COUNT(a)');
        $qb->where('a.openId = :openId');
        $qb->setParameter('openId', $user_data->openid);
        $count = $qb->getQuery()->getSingleScalarResult();
        if($count <= 0){
            $wechat_user = new Entity\WechatUser();
            $wechat_user->setOpenId($wechat_openid);
            $wechat_user->setNickName($user_data->nickname);
            $wechat_user->setCity($user_data->city);
            $wechat_user->setGender($user_data->sex);
            $wechat_user->setProvince($user_data->province);
            $wechat_user->setCountry($user_data->country);
            $wechat_user->setHeadImg($user_data->headimgurl);
            $wechat_user->setCreateIp($request->getClientIp());
            $wechat_user->setCreateTime(new \DateTime('now'));
            $em->persist($wechat_user);
            $em->flush();
        }
        else{
            $wechat_user = $em->getRepository('AppBundle:WechatUser')->findOneBy(array('openId' => $wechat_openid));
            $session->set('user_id', $wechat_user->getId());
        }

        $redirect_url = $session->get('redirect_url') == null ? $this->generateUrl('_index') : $session->get('redirect_url');
        $em->getConnection()->commit();
        return $this->redirect($redirect_url);
    }
    catch (Exception $e) {
        $em->getConnection()->rollback();
        return new Response($e->getMessage());
    }
  }
}
