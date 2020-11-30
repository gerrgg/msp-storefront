import Slideout from "slideout";

const MobileMenu = (() => {
  // init slideout object
  const slideoutMenu = new Slideout({
    panel: document.getElementById("page"),
    menu: document.getElementById("mobile-menu"),
    padding: 256,
    tolerance: 70,
    touch: false,
  });

  // slideout object
  slideoutMenu
    .on("beforeopen", function (e) {
      this.panel.classList.add("panel-open");
    })
    .on("open", function (e) {
      this.panel.addEventListener("click", closeMenu);
    })
    .on("beforeclose", function (e) {
      this.panel.classList.remove("panel-open");
      this.panel.removeEventListener("click", closeMenu);
    });

  // get mobile button
  const menuButton = document.querySelector(".mobile-menu-button");
  // shows mobile menu
  const showMenu = (event) => {
    event.preventDefault();
    slideoutMenu.menu.style.display = "block";
    slideoutMenu.toggle();
  };
  // add event listener
  menuButton.addEventListener("click", showMenu);

  // get close button
  const closeButton = document.querySelector("a.close");
  // closes the menu
  const closeMenu = (event) => slideoutMenu.close();
  // run closemenu on when close button is clicked
  closeButton.addEventListener("click", closeMenu);
})();
