{
    const swiper = new Swiper(".swiper",{
        loop:true,
        // initialSlide:2,
        // direction:"vertical",
        speed:300,
        effect:"slide",
        centeredSlides: true,
        slidesPerView: 1,
        breakpoints: {
            768: { // 768px以下
                slidesPerView: 3, // 表示するスライドを1枚に変更
                centeredSlides: true,
            },
        },
        spaceBetween: 100,
        centeredSlides: false,
        grabCursor: true,
        // autoplay: {
        //     delay: 1000,
        // },
        pagination: {
            el:".swiper-pagination"
        },
        navigation:{
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev"
        }
    }); 
}