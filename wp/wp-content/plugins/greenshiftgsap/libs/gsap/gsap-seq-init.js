function GSSeqinit(t,e=!1,a=!1,r=""){let i=r||document;var g={};if(t.getAttribute("data-triggertype"))var d=t.getAttribute("data-triggertype");else d="scroll";let u=[],s=t.getAttribute("data-imagesexternal"),n=[];if(s?(s=s.trim(),u=s.split(",")):u=JSON.parse(t.getAttribute("data-images")),!u.length)return;u.forEach(t=>{let e=new Image;e.src=t.trim(),n.push(e)});let l=t;gsap.set(l,{attr:{"data-index":0}});let o={};if(t.getAttribute("data-delay")&&(o.delay=parseFloat(t.getAttribute("data-delay"))),t.getAttribute("data-duration")&&(o.duration=parseFloat(t.getAttribute("data-duration"))),"none"==t.getAttribute("data-ease"))o.ease="none";else if(t.getAttribute("data-ease")){let e=t.getAttribute("data-ease").split("-");o.ease=e[0]+"."+e[1]}o.immediateRender=!0,o.attr={"data-index":u.length-1},o.snap="data-index";let c=t.getAttribute("data-rendertype"),b=c&&"canvas"==c?t.querySelector("canvas"):"",A=b?b.getContext("2d"):"";if(b){b.width=t.getAttribute("data-canvaswidth"),b.height=t.getAttribute("data-canvasheight");const e=()=>{A.clearRect(0,0,b.width,b.height),A.drawImage(n[0],0,0)};n[0].onload=e}o.onUpdate=function(){if(b){let t=b.parentNode.getAttribute("data-index");A.clearRect(0,0,b.width,b.height),A.drawImage(n[t],0,0)}else{let e=t.querySelector("img").parentNode.getAttribute("data-index");t.querySelector("img").setAttribute("src",u[e])}};let p=gsap.timeline();if(p.to(l,o),"yes"==t.getAttribute("data-loop")&&("yes"==t.getAttribute("data-yoyo")&&p.yoyo(!0),p.repeat(-1),t.getAttribute("data-delay")&&"yes"==t.getAttribute("repeat-delay")&&p.repeatDelay(parseFloat(t.getAttribute("data-delay")))),"load"==d||e)p.play();else if("hover"==d){let e="";if(t.getAttribute("data-customtrigger")){if(0==t.getAttribute("data-customtrigger").indexOf("#")||0==t.getAttribute("data-customtrigger").indexOf("."))var m=t.getAttribute("data-customtrigger");else m="#"+t.getAttribute("data-customtrigger");e=i.querySelector(m)}else e=t;p.pause(),p.reverse(),e&&(e.addEventListener("mouseenter",function(t){p.play()}),e.addEventListener("mouseleave",function(e){t.getAttribute("data-hoverpause")?p.pause():p.reverse()}))}else if("click"==d){let e="";if(t.getAttribute("data-customtrigger")){if(0==t.getAttribute("data-customtrigger").indexOf("#")||0==t.getAttribute("data-customtrigger").indexOf("."))m=t.getAttribute("data-customtrigger");else m="#"+t.getAttribute("data-customtrigger");e=i.querySelector(m)}else e=t;p.pause(),p.reverse(),e&&e.addEventListener("click",function(t){p.play()})}else{if(t.getAttribute("data-customtrigger"))if(0==t.getAttribute("data-customtrigger").indexOf("#")||0==t.getAttribute("data-customtrigger").indexOf("."))m=t.getAttribute("data-customtrigger");else m="#"+t.getAttribute("data-customtrigger");else m=t;if(g.trigger=m,t.getAttribute("data-triggerstart")?g.start=t.getAttribute("data-triggerstart"):g.start="top 92%",t.getAttribute("data-triggerend")&&(g.end=t.getAttribute("data-triggerend"),g.scrub=1,t.getAttribute("data-triggerscrub")&&(g.scrub=parseFloat(t.getAttribute("data-triggerscrub")))),t.getAttribute("data-pinned")&&(g.pin=!0),t.getAttribute("data-pinspace")&&(g.pinSpacing=!1),t.getAttribute("data-triggeraction")?g.toggleActions=t.getAttribute("data-triggeraction"):g.toggleActions="play pause resume reverse",g.animation=p,g.fastScrollEnd=!0,a){let t=".interface-interface-skeleton__content";g.scroller=t}ScrollTrigger.create(g)}}document.addEventListener("DOMContentLoaded",function(t){let e=document.getElementsByClassName("gs-sequencer-wrap");if(e.length>0)for(let t=0;t<e.length;t++){GSSeqinit(e[t])}});