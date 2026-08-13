(() => {
"use strict";

const header=document.querySelector("[data-contact-header]");
const button=document.querySelector("[data-contact-menu-button]");
const mobile=document.querySelector("[data-contact-mobile-nav]");

const updateHeader=()=>{if(header)header.classList.toggle("is-scrolled",window.scrollY>16)};
updateHeader();
window.addEventListener("scroll",updateHeader,{passive:true});

if(button&&mobile){
    const close=()=>{button.setAttribute("aria-expanded","false");mobile.hidden=true};
    button.addEventListener("click",()=>{
        const expanded=button.getAttribute("aria-expanded")==="true";
        button.setAttribute("aria-expanded",expanded?"false":"true");
        mobile.hidden=expanded;
    });
    mobile.addEventListener("click",event=>{if(event.target.closest("a"))close()});
    window.addEventListener("resize",()=>{if(innerWidth>1024)close()});
}

const reveal=[...document.querySelectorAll("[data-contact-reveal]")];
if(matchMedia("(prefers-reduced-motion: reduce)").matches||!("IntersectionObserver"in window)){
    reveal.forEach(node=>node.classList.add("is-visible"));
}else{
    const observer=new IntersectionObserver(entries=>entries.forEach(entry=>{
        if(entry.isIntersecting){entry.target.classList.add("is-visible");observer.unobserve(entry.target)}
    }),{threshold:.1});
    reveal.forEach(node=>observer.observe(node));
}

const message=document.querySelector("[data-contact-message]");
const count=document.querySelector("[data-message-count]");

if(message&&count){
    const updateCount=()=>{count.textContent=`${message.value.length} / 2000`};
    updateCount();
    message.addEventListener("input",updateCount);
}
})();