(() => {
"use strict";
const h=document.querySelector("[data-events-header]");
const b=document.querySelector("[data-events-menu-button]");
const m=document.querySelector("[data-events-mobile-nav]");
const update=()=>{if(h)h.classList.toggle("is-scrolled",window.scrollY>16)};
update();window.addEventListener("scroll",update,{passive:true});
if(b&&m){
 const close=()=>{b.setAttribute("aria-expanded","false");m.hidden=true};
 b.addEventListener("click",()=>{const x=b.getAttribute("aria-expanded")==="true";b.setAttribute("aria-expanded",x?"false":"true");m.hidden=x});
 m.addEventListener("click",e=>{if(e.target.closest("a"))close()});
 window.addEventListener("resize",()=>{if(innerWidth>1024)close()});
}
const nodes=[...document.querySelectorAll("[data-events-reveal]")];
if(matchMedia("(prefers-reduced-motion: reduce)").matches||!("IntersectionObserver"in window)){nodes.forEach(n=>n.classList.add("is-visible"));return}
const io=new IntersectionObserver(es=>es.forEach(e=>{if(e.isIntersecting){e.target.classList.add("is-visible");io.unobserve(e.target)}}),{threshold:.1});
nodes.forEach(n=>io.observe(n));
})();