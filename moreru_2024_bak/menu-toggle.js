
document.addEventListener("click", function(event) {
    const slideMenu = document.querySelector(".slide-menu");
    const slideMenuUl = document.querySelector("#slide-menu ul");
    const menuIcon = document.getElementById("menu-icon");
    const buyTickets = document.querySelector(".buy_tickets");

    // `menu-icon` をクリックしたときの処理
    if (menuIcon.contains(event.target)) {
        slideMenu.classList.toggle("active");
        menuIcon.classList.toggle("active");

        // メニューテキストを切り替え
        const menutext = menuIcon.querySelector(".menu-text");
        menutext.textContent = slideMenu.classList.contains("active") ? "close" : "menu";

        // buyTickets の表示を切り替え
        buyTickets.style.display = slideMenu.classList.contains("active") ? "none" : "block";

        // これ以上のイベント伝播を防ぐ
        event.stopPropagation();
        return;
    }

    // メニューがアクティブであり、クリックした場所がメニュー外の場合
    if (slideMenu.classList.contains("active")) {
        if (!slideMenuUl.contains(event.target) && !menuIcon.contains(event.target)) {
            slideMenu.classList.remove("active");
            menuIcon.classList.remove("active");

            // メニューテキストを元に戻す
            const menutext = menuIcon.querySelector(".menu-text");
            menutext.textContent = "menu";
            buyTickets.style.display = "block";
        }
    }
});


window.onload = function() {
    const writingWrap = document.querySelector('.writing_wrap');
    writingWrap.scrollLeft = writingWrap.scrollWidth;

    const writing_wrap_sec = document.querySelector('.writing_wrap_sec');
    writing_wrap_sec.scrollLeft = writing_wrap_sec.scrollWidth;
};

//スクロール量で背景画像を変化させるJS（これだとフワッと出てくるアニメーションが効かないのでコメントアウト）
// window.addEventListener('scroll', function() {
//     const targetDiv = document.getElementById('test_test5');
// if (targetDiv) {
//     const rect = targetDiv.getBoundingClientRect();
//     const scrollPosition = rect.top + window.scrollY;
//     const triggerPosition = -1200;

//     console.log("現在のrect.top:", rect.top);
//     console.log("現在のwindow.scrollY:", window.scrollY);
//     console.log("現在のscrollPosition:", scrollPosition); 

//     if(rect.top >= (triggerPosition)){
//         targetDiv.style.backgroundImage = "url('./img/1.jpg')";
//     }else{
//         targetDiv.style.backgroundImage = "url('./img/3.jpg')";
//     }

//     console.log("現在の背景画像:", targetDiv.style.backgroundImage); 
// } else {
//     console.log("false");
// }
// })

window.addEventListener('scroll', function() {
    const target = document.querySelector('.test_test5');
    const rect = target.getBoundingClientRect();
    const windowHeight = window.innerHeight;
  
    // 要素が画面内に入ったら透明度を1にする
    if (rect.top < windowHeight) {
      target.style.opacity = 1;
    }else{
      target.style.opacity = 0;
    }
  });

  window.addEventListener('scroll', function () {
    const targetDiv = document.querySelector('.third_content');
    const rect = targetDiv.getBoundingClientRect();
    const scrollPosition = window.scrollY;
    const targetPosition = rect.top + scrollPosition; // test_test5の上端のスクロール位置
    const triggerPosition = targetPosition + 800; // test_test5の上端から400px下の位置
  
    if (scrollPosition >= triggerPosition) {
      targetDiv.classList.add('fade-in');
    } else {
      targetDiv.classList.remove('fade-in');
    }
  });
  