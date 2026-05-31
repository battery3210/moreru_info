// const burger = document.querySelector(".burger");
// const nav = document.querySelector(".header_ul");
// const navLinks = document.querySelectorAll(".nav-links li");


// burger.addEventListener("click",(event)=>{
//    // alert('checkFirst!!');
//     burger.classList.toggle("toggle");

//     nav.classList.toggle("ul_active");

//     // メニューがアクティブであり、クリックした場所がメニュー外の場合
//     if (nav.classList.contains("ul_active")) {
//         if (!nav.contains(event.target) && !burger.contains(event.target)) {
//             alert('check!!');
//             nav.classList.remove("ul_active");
//             burger.classList.remove("toggle");

//             // メニューテキストを元に戻す
//             // const menutext = menuIcon.querySelector(".menu-text");
//             // menutext.textContent = "menu";
//             // buyTickets.style.display = "block";
//         }
//     }
 
// });

document.addEventListener("click", function(event) {
    //alert('firstSuccess');
    const burger = document.querySelector(".burger");
    const nav = document.querySelector(".header__nav");
    const navLinks = document.querySelectorAll(".header__nav ul");

    // `burger` をクリックしたときの処理
    if (burger.contains(event.target)) {
        burger.classList.toggle("toggle");
        nav.classList.toggle("header__nav-active");

        // これ以上のイベント伝播を防ぐ
        event.stopPropagation();
        return;
    }

    // メニューがアクティブであり、クリックした場所がメニュー外の場合
    if (nav.classList.contains("header__nav-active")) {
        //alert('SecondSuccess');
        if (!nav.contains(event.target) && !burger.contains(event.target)) {
            burger.classList.remove("toggle");
            nav.classList.remove("header__nav-active");

        }
    }
});

// トップに戻るボタン用
const backToTop = document.getElementById("backToTop");

window.addEventListener("scroll", () => {
    if (window.scrollY > 300) {
        backToTop.classList.add("show");
    } else {
        backToTop.classList.remove("show");
    }
});

backToTop.addEventListener("click", () => {
    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });
});

const loading = document.querySelector(".loading");

window.onload = () => {
    setTimeout(function(){
        loading.style.display = "none";
    },2000);
};

function setFullHeight() {
            const vh = window.innerHeight * 0.01;
            document.documentElement.style.setProperty('--vh', `${vh}px`);
}

window.addEventListener('load', setFullHeight);
window.addEventListener('resize', setFullHeight);


// トップに戻るボタン用END

// document.addEventListener("click", handleMenuToggle);
// document.addEventListener("touchstart", handleMenuToggle);

// function handleMenuToggle(event) {
//     const burger = document.querySelector(".burger");
//     const nav = document.querySelector(".header_ul");
// //alert('new_suc');
//     // `burger` をクリック/タップしたときの処理
//     if (burger.contains(event.target)) {
//         burger.classList.toggle("toggle");
//         nav.classList.toggle("ul_active");

//         // これ以上のイベント伝播を防ぐ
//         event.stopPropagation();
//         return;
//     }

//     // メニューがアクティブであり、クリック/タップした場所がメニュー外の場合
//     // if (nav.classList.contains("ul_active")) {
//     //     //alert('new_suc');
//     //     if (!nav.contains(event.target) && !burger.contains(event.target)) {
//     //         burger.classList.remove("toggle");
//     //         nav.classList.remove("ul_active");
//     //     }
//     // }
// }




