
window.onscroll = function() {scrollFuntion()};

function scrollFuntion() {
  if (document.documentElement.scrollTop > 200) {
    document.getElementById("casabella-navbar").classList.add("stickyHeader");
  } else {
    document.getElementById("casabella-navbar").classList.remove("stickyHeader");
  }
}
var swiper = new Swiper(".mySwiper", {
    cssMode: true,
    draggable: true,
    loop : true,
    autoplay: {
        delay: 3000,
    },
    navigation: {
        nextEl: ".swiper-button-next",
        prevEl: ".swiper-button-prev",
        clickable: true,
    },
    pagination: {
        clickable: true,
        el: ".swiper-pagination",
    },
});