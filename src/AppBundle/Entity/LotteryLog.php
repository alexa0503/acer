<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="t_lottery_log")
 */
class LotteryLog
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="WechatUser", inversedBy="lotteryLogs")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;
   /**
     * @ORM\Column(name="lottery_type",type="string", length=20)
     */
    protected $type;
   /**
     * @ORM\Column(name="award_type",type="string", length=20)
     */
    protected $awardType;
   /**
     * @ORM\Column(name="hasWin",type="boolean")
     */
    protected $hasWin;
    /**
     * @ORM\Column(name="create_time",type="datetime")
     */
    protected $createTime;
    /**
     * @ORM\Column(name="create_ip",type="string", length=60)
     */
    protected $createIp;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return LotteryLog
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     * @return LotteryLog
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * Get createTime
     *
     * @return \DateTime 
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * Set createIp
     *
     * @param string $createIp
     * @return LotteryLog
     */
    public function setCreateIp($createIp)
    {
        $this->createIp = $createIp;

        return $this;
    }

    /**
     * Get createIp
     *
     * @return string 
     */
    public function getCreateIp()
    {
        return $this->createIp;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\WechatUser $user
     * @return LotteryLog
     */
    public function setUser(\AppBundle\Entity\WechatUser $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\WechatUser 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set hasWin
     *
     * @param boolean $hasWin
     * @return LotteryLog
     */
    public function setHasWin($hasWin)
    {
        $this->hasWin = $hasWin;

        return $this;
    }

    /**
     * Get hasWin
     *
     * @return boolean 
     */
    public function getHasWin()
    {
        return $this->hasWin;
    }

    /**
     * Set awardType
     *
     * @param string $awardType
     * @return LotteryLog
     */
    public function setAwardType($awardType)
    {
        $this->awardType = $awardType;

        return $this;
    }

    /**
     * Get awardType
     *
     * @return string 
     */
    public function getAwardType()
    {
        return $this->awardType;
    }
}
