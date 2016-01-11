//找到url中匹配的字符串
function findInUrl(str){
	url = location.href;
	return url.indexOf(str) == -1 ? false : true;
}
//获取url参数
function queryString(key){
    return (document.location.search.match(new RegExp("(?:^\\?|&)"+key+"=(.*?)(?=&|$)"))||['',null])[1];
}

//产生指定范围的随机数
function randomNumb(minNumb,maxNumb){
	var rn=Math.round(Math.random()*(maxNumb-minNumb)+minNumb);
	return rn;
	}
	
var wHeight;
$(document).ready(function(){
	wHeight=$(window).height();
	if(wHeight<1008){
		wHeight=1008;
		}
		
	$('.outer').height(wHeight);
	$('.shareNote').height(wHeight);
	$('.h1008').css('padding-top',(wHeight-1008)/2+'px');
	});
	
function closeRule(){
	$('.rule').fadeOut(500);
	$('.logo').fadeIn(500);
	}
	
function showRule(){
	$('.rule').fadeIn(500);
	$('.logo').fadeOut(500);
	}
	
function showOnce(){
	$('.popBg').show();
	$('.onceAlert').fadeIn(500);
	}

function closeOncePop(){
	$('.popBg').hide();
	$('.onceAlert').fadeOut(500);
	canLotteryA=true;
	canLotteryB=true;
	}
	
function showShareNote(){
	$('.shareNote').fadeIn(500);
	}

function closeShareNote(){
	$('.shareNote').fadeOut(500);
	}




