document.addEventListener("DOMContentLoaded", function () {
    // Select all nodes
    const nodes = document.querySelectorAll(".tree li.node");
  
    // Function to apply filters
    function apllyFilters(category) {
      nodes.forEach((node) => {
        if (category === "all") {
          node.classList.add("active");
        } else {
          if (node.classList.contains(category)) {
            node.classList.add("active");
          } else {
            node.classList.remove("active");
          }
        }
      });
    }
  
    apllyFilters("all");
  
    // Add event listener to all categories
    const categories = document.querySelectorAll(".categories .category");
  
    categories.forEach((category) => {
      // mouseenter and mouseleave events to highlight categories
      category.addEventListener("mouseenter", () => {
        categories.forEach((item) => {
          if (item !== category) {
            item.style.opacity = "0.25";
          }
        });
      });
      category.addEventListener("mouseleave", () => {
        categories.forEach((item) => {
          if (item !== category) {
            item.style.opacity = "1";
          }
        });
      });
  
      // click event on category
      category.addEventListener("click", () => {
        // Select the selected category
        const selectedCategoryId = category.id;
  
        // If the category is already active, remove the active class
        if (category.classList.contains("active")) {
          apllyFilters("all"); // Reset the filters
        } else {
          // If the category is not active, apply the active class
          categories.forEach((item) => {
            item.classList.remove("active"); // Remove the active class, only if the category is not active
          });
  
          // Add the active class
          category.classList.add("active");
          // Apply the filters
          apllyFilters(selectedCategoryId);
  
          // Scroll to the selected category
          const selectedCategory = document.getElementById(selectedCategoryId);
          selectedCategory.scrollIntoView({ behavior: "smooth" });
  
          // Scroll to the top
          window.scrollTo({ top: 0, behavior: "smooth" });
        }
      });
    });
  });
  