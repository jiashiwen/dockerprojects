fnResize();
var k = null;
window.addEventListener("resize",function(){clearTimeout(k);k = setTimeout(fnResize,300);},false);
function fnResize(){document.getElementsByTagName('html')[0].style.fontSize = (document.documentElement.clientWidth) / 15 + 'px';}