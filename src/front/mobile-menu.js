import Slideout from "slideout";

const mobileMenu = () => {
  // Build slideout object
  const slideoutMenu = new Slideout({
    panel: document.getElementById("page"),
    menu: document.getElementById("mobile-menu"),
    padding: 256,
    tolerance: 70,
    touch: false,
  });

  const showMenu = (event) => {
    event.preventDefault();
    slideoutMenu.menu.style.display = "block";
    slideoutMenu.toggle();
  };

  const closeMenu = (event) => slideoutMenu.close();

  /**
   * Listeners
   */
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

  /**
   * Open Menu
   */
  const menuButton = document.querySelector(".mobile-menu-button");
  if (menuButton) {
    menuButton.addEventListener("click", showMenu);
  }

  /**
   * Close Menu
   */
  const closeButton = document.querySelector("a.close");
  if (closeButton) {
    closeButton.addEventListener("click", closeMenu);
  }
};

export default mobileMenu;
