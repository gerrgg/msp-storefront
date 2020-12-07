import "/node_modules/tiny-slider/dist/tiny-slider.css";
import { tns } from "/node_modules/tiny-slider/src/tiny-slider.js";

const slider = () => {
  const sliders = document.querySelectorAll(".owl-carousel");

  if (sliders) {
    for (let slider of sliders) {
      tns({
        fixedWidth: 200,
        container: slider,
        items: 6,
        nav: false,
        responsive: {
          640: {
            items: 2,
          },
        },
      });
    }
  }
};

export default slider;
