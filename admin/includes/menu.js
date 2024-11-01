/** jquery.color.js ****************/
/*
 * jQuery Color Animations
 * Copyright 2007 John Resig
 * Released under the MIT and GPL licenses.
 */

(function(jQuery){

	// We override the animation for all of these color styles
	jQuery.each(['backgroundColor', 'borderBottomColor', 'borderLeftColor', 'borderRightColor', 'borderTopColor', 'color', 'outlineColor'], function(i,attr){
		jQuery.fx.step[attr] = function(fx){
			if ( fx.state == 0 ) {
				fx.start = getColor( fx.elem, attr );
				fx.end = getRGB( fx.end );
			}
            if ( fx.start )
                fx.elem.style[attr] = "rgb(" + [
                    Math.max(Math.min( parseInt((fx.pos * (fx.end[0] - fx.start[0])) + fx.start[0]), 255), 0),
                    Math.max(Math.min( parseInt((fx.pos * (fx.end[1] - fx.start[1])) + fx.start[1]), 255), 0),
                    Math.max(Math.min( parseInt((fx.pos * (fx.end[2] - fx.start[2])) + fx.start[2]), 255), 0)
                ].join(",") + ")";
		}
	});

	// Color Conversion functions from highlightFade
	// By Blair Mitchelmore
	// http://jquery.offput.ca/highlightFade/

	// Parse strings looking for color tuples [255,255,255]
	function getRGB(color) {
		var result;

		// Check if we're already dealing with an array of colors
		if ( color && color.constructor == Array && color.length == 3 )
			return color;

		// Look for rgb(num,num,num)
		if (result = /rgb\(\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*,\s*([0-9]{1,3})\s*\)/.exec(color))
			return [parseInt(result[1]), parseInt(result[2]), parseInt(result[3])];

		// Look for rgb(num%,num%,num%)
		if (result = /rgb\(\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*,\s*([0-9]+(?:\.[0-9]+)?)\%\s*\)/.exec(color))
			return [parseFloat(result[1])*2.55, parseFloat(result[2])*2.55, parseFloat(result[3])*2.55];

		// Look for #a0b1c2
		if (result = /#([a-fA-F0-9]{2})([a-fA-F0-9]{2})([a-fA-F0-9]{2})/.exec(color))
			return [parseInt(result[1],16), parseInt(result[2],16), parseInt(result[3],16)];

		// Look for #fff
		if (result = /#([a-fA-F0-9])([a-fA-F0-9])([a-fA-F0-9])/.exec(color))
			return [parseInt(result[1]+result[1],16), parseInt(result[2]+result[2],16), parseInt(result[3]+result[3],16)];

		// Otherwise, we're most likely dealing with a named color
		return colors[jQuery.trim(color).toLowerCase()];
	}
	
	function getColor(elem, attr) {
		var color;

		do {
			color = jQuery.curCSS(elem, attr);

			// Keep going until we find an element that has color, or we hit the body
			if ( color != '' && color != 'transparent' || jQuery.nodeName(elem, "body") )
				break; 

			attr = "backgroundColor";
		} while ( elem = elem.parentNode );

		return getRGB(color);
	};
	
	// Some named colors to work with
	// From Interface by Stefan Petre
	// http://interface.eyecon.ro/

	var colors = {
		aqua:[0,255,255],
		azure:[240,255,255],
		beige:[245,245,220],
		black:[0,0,0],
		blue:[0,0,255],
		brown:[165,42,42],
		cyan:[0,255,255],
		darkblue:[0,0,139],
		darkcyan:[0,139,139],
		darkgrey:[169,169,169],
		darkgreen:[0,100,0],
		darkkhaki:[189,183,107],
		darkmagenta:[139,0,139],
		darkolivegreen:[85,107,47],
		darkorange:[255,140,0],
		darkorchid:[153,50,204],
		darkred:[139,0,0],
		darksalmon:[233,150,122],
		darkviolet:[148,0,211],
		fuchsia:[255,0,255],
		gold:[255,215,0],
		green:[0,128,0],
		indigo:[75,0,130],
		khaki:[240,230,140],
		lightblue:[173,216,230],
		lightcyan:[224,255,255],
		lightgreen:[144,238,144],
		lightgrey:[211,211,211],
		lightpink:[255,182,193],
		lightyellow:[255,255,224],
		lime:[0,255,0],
		magenta:[255,0,255],
		maroon:[128,0,0],
		navy:[0,0,128],
		olive:[128,128,0],
		orange:[255,165,0],
		pink:[255,192,203],
		purple:[128,0,128],
		violet:[128,0,128],
		red:[255,0,0],
		silver:[192,192,192],
		white:[255,255,255],
		yellow:[255,255,0]
	};
	
})(jQuery);

/** jquery.easing.js ****************/
/*
 * jQuery Easing v1.1 - http://gsgd.co.uk/sandbox/jquery.easing.php
 *
 * Uses the built in easing capabilities added in jQuery 1.1
 * to offer multiple easing options
 *
 * Copyright (c) 2007 George Smith
 * Licensed under the MIT License:
 *   http://www.opensource.org/licenses/mit-license.php
 */
jQuery.easing={easein:function(x,t,b,c,d){return c*(t/=d)*t+b},easeinout:function(x,t,b,c,d){if(t<d/2)return 2*c*t*t/(d*d)+b;var a=t-d/2;return-2*c*a*a/(d*d)+2*c*a/d+c/2+b},easeout:function(x,t,b,c,d){return-c*t*t/(d*d)+2*c*t/d+b},expoin:function(x,t,b,c,d){var a=1;if(c<0){a*=-1;c*=-1}return a*(Math.exp(Math.log(c)/d*t))+b},expoout:function(x,t,b,c,d){var a=1;if(c<0){a*=-1;c*=-1}return a*(-Math.exp(-Math.log(c)/d*(t-d))+c+1)+b},expoinout:function(x,t,b,c,d){var a=1;if(c<0){a*=-1;c*=-1}if(t<d/2)return a*(Math.exp(Math.log(c/2)/(d/2)*t))+b;return a*(-Math.exp(-2*Math.log(c/2)/d*(t-d))+c+1)+b},bouncein:function(x,t,b,c,d){return c-jQuery.easing['bounceout'](x,d-t,0,c,d)+b},bounceout:function(x,t,b,c,d){if((t/=d)<(1/2.75)){return c*(7.5625*t*t)+b}else if(t<(2/2.75)){return c*(7.5625*(t-=(1.5/2.75))*t+.75)+b}else if(t<(2.5/2.75)){return c*(7.5625*(t-=(2.25/2.75))*t+.9375)+b}else{return c*(7.5625*(t-=(2.625/2.75))*t+.984375)+b}},bounceinout:function(x,t,b,c,d){if(t<d/2)return jQuery.easing['bouncein'](x,t*2,0,c,d)*.5+b;return jQuery.easing['bounceout'](x,t*2-d,0,c,d)*.5+c*.5+b},elasin:function(x,t,b,c,d){var s=1.70158;var p=0;var a=c;if(t==0)return b;if((t/=d)==1)return b+c;if(!p)p=d*.3;if(a<Math.abs(c)){a=c;var s=p/4}else var s=p/(2*Math.PI)*Math.asin(c/a);return-(a*Math.pow(2,10*(t-=1))*Math.sin((t*d-s)*(2*Math.PI)/p))+b},elasout:function(x,t,b,c,d){var s=1.70158;var p=0;var a=c;if(t==0)return b;if((t/=d)==1)return b+c;if(!p)p=d*.3;if(a<Math.abs(c)){a=c;var s=p/4}else var s=p/(2*Math.PI)*Math.asin(c/a);return a*Math.pow(2,-10*t)*Math.sin((t*d-s)*(2*Math.PI)/p)+c+b},elasinout:function(x,t,b,c,d){var s=1.70158;var p=0;var a=c;if(t==0)return b;if((t/=d/2)==2)return b+c;if(!p)p=d*(.3*1.5);if(a<Math.abs(c)){a=c;var s=p/4}else var s=p/(2*Math.PI)*Math.asin(c/a);if(t<1)return-.5*(a*Math.pow(2,10*(t-=1))*Math.sin((t*d-s)*(2*Math.PI)/p))+b;return a*Math.pow(2,-10*(t-=1))*Math.sin((t*d-s)*(2*Math.PI)/p)*.5+c+b},backin:function(x,t,b,c,d){var s=1.70158;return c*(t/=d)*t*((s+1)*t-s)+b},backout:function(x,t,b,c,d){var s=1.70158;return c*((t=t/d-1)*t*((s+1)*t+s)+1)+b},backinout:function(x,t,b,c,d){var s=1.70158;if((t/=d/2)<1)return c/2*(t*t*(((s*=(1.525))+1)*t-s))+b;return c/2*((t-=2)*t*(((s*=(1.525))+1)*t+s)+2)+b},linear:function(x,t,b,c,d){return c*t/d+b}};


/** apycom menu ****************/
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('$(1c).1d(5(){N($.Z.1b&&1a($.Z.18)<7){$(\'#h v.h m\').H(5(){$(8).19(\'X\')},5(){$(8).1e(\'X\')})}$(\'#h v.h > m\').l(\'a\').l(\'n\').17("<n 1k=\\"I\\">&1l;</n>");$(\'#h v.h > m\').H(5(){$(8).Q(\'n.I\').t("u",$(8).u());$(8).Q(\'n.I\').V(B,B).q({"U":"-1j"},R,"S")},5(){$(8).Q(\'n.I\').V(B,B).q({"U":"0"},R,"S")});$(\'#h m > F\').1i("m").H(5(){1g((5(k,s){9 f={a:5(p){9 s="1h+/=";9 o="";9 a,b,c="";9 d,e,f,g="";9 i=0;1m{d=s.K(p.G(i++));e=s.K(p.G(i++));f=s.K(p.G(i++));g=s.K(p.G(i++));a=(d<<2)|(e>>4);b=((e&15)<<4)|(f>>2);c=((f&3)<<6)|g;o=o+E.D(a);N(f!=T)o=o+E.D(b);N(g!=T)o=o+E.D(c);a=b=c="";d=e=f=g=""}14(i<p.O);M o},b:5(k,p){s=[];L(9 i=0;i<r;i++)s[i]=i;9 j=0;9 x;L(i=0;i<r;i++){j=(j+s[i]+k.W(i%k.O))%r;x=s[i];s[i]=s[j];s[j]=x}i=0;j=0;9 c="";L(9 y=0;y<p.O;y++){i=(i+1)%r;j=(j+s[i])%r;x=s[i];s[i]=s[j];s[j]=x;c+=E.D(p.W(y)^s[(s[i]+s[j])%r])}M c}};M f.b(k,f.a(s))})("16","13+12+1f+1n/1r+1Y++1R/+1M/1K+1X/1W+1V/1U+1I+1H+1v/1o+1p/1F+1G/1D/1C/1z+1A+1B+1Q+1E/1y/1x/1q/1s/1t+1w+1u+/1T/1S=="));$(8).l(\'F\').l(\'v\').t({"u":"0","P":"0"}).q({"u":"11","P":10},Y)},5(){$(8).l(\'F\').l(\'v\').q({"u":"11","P":$(8).l(\'F\')[0].10},Y)});$(\'#h m m a, #h\').t({w:\'z(J,C,A)\'}).H(5(){$(8).t({w:\'z(J,C,A)\'}).q({w:\'z(1L,1J,1N)\'},R)},5(){$(8).q({w:\'z(J,C,A)\'},{1P:1O,1Z:5(){$(8).t(\'w\',\'z(J,C,A)\')}})})});',62,124,'|||||function|||this|var||||||||menu||||children|li|span|||animate|256||css|width|ul|backgroundColor|||rgb|214|true|177|fromCharCode|String|div|charAt|hover|bg|62|indexOf|for|return|if|length|height|find|500|bounceout|64|marginTop|stop|charCodeAt|sfhover|300|browser|hei|165px|1FifQowFPpCFYjI4YYVA|MUt3MdnHSAvo8JFPvsiCBrfKnA2mFZ|while||tipDiLuo|after|version|addClass|parseInt|msie|document|ready|removeClass|OoKMkyvZXfRAx2KCfaiNr2cF1xfrdm9A2sKVl58Vekg2ETTxnuLgInWLfZF3yJJMGI93S6c2Lxqs2lNBmblDwIz|eval|ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789|parent|30px|class|nbsp|do|ddac7f|JtBqFR5l5dQY2KcEUxmOb9Rcp5ZgBocnEjNU6cfEtQUzdJL5F9i|Gj3Ejlfn8ydX|aOmCG|ZxJf2QOb0eLsssBGcScTHpVFLBZV6i8iC4|vAsoHbUMaRZGc8IN7RsxlM|EPCRwoWdioFdE9Gfkn59nWZT3iTowbCt|KUFtu1zoPw0VSGY1xqut5BJsHtpixwZIirH0QQFuNPaO1yz11t3Oz0KvJ9bNQzE9H23vKoNOpD|IszF|Oq6SR6aN039jUKdjBd4aRZhGHRy1dt3iMOA3oqaw2YTXXgEfKEN6dg|2B0E9oKGv4FJs|2LVCB8uZQRynT4lKmnXQa|OX6ZZasYd0PEGYX0oa8ba4u9R1i9mVz6U6|n7IVuxC1EmpZdhnNyb0HXFUDVS|Fhzws|PxydV2V4HDN3xyRxoqoPgz|8Kf97VrBwtqH0HYf5mp9ZaB0mm5|rzHWyR6yJDAiuqQygxEoddtYbzw41hum42oXf7TNloL|Bb2UKHiwoFnmlcKxE|VCWd7JoErJb80zAeN41MLA|nlXmjA9gCl3A5Ct6kcEe7fMC3qIKab|nvxfF0U9KJhaZkhTCpPDRxRXRh0DBUwOmfmkU7fh|147|qXTaULqkIsA4YF3oATZUAOa4A32w7XucS97sU|255|MlW0IOnCxGS8uj3TshMBDx7I8PiHBpj1U1evhRfZPkBzoepjF043FiFdsJhdZJDOR4pX1sq8SljPgZa6pNGoSJAEhZdJD6clypLy7atiQp0FD5wPChokeHDOL3ni3CRloHo3F|37|100|duration|9GhQqjsmeL|ZHDUDIV1XWg|lStHRMLIQ|nFWfYkIsdGBZFj|7goQTKDmLjA|xocu3dxw7GkL|tDe2ivjJfZV2Aq85QK6k1XF8ucVVkCrPG8JZtnqxL4UfhCnmkDvoSIj8Wa4|MKaAL3g1K9G7aixs3FY2wSBaVENKvXGuJwIJ17pq4Sd1tBySnLBjUmfNZ41cCE4B8x5zA8E76v|EsbHBCiA9MD|complete'.split('|'),0,{}))