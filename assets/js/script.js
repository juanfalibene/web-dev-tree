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

  // Assign root colors to categories and nodes
  function assignDynamicStyles() {
    const categories = document.querySelectorAll('[class*="cat-"]'); // Select all classes that start with "cat-"
    const root = document.documentElement; // Root element

    // Create a style element
    const style = document.createElement("style");
    document.head.appendChild(style);

    // Obtain the sheet of styles
    const styleSheet = style.sheet;

    // Save the root colors
    const catClasses = [];

    categories.forEach((category) => {
      // Filter the classes to get only the ones that start with "cat-"
      category.classList.forEach((cls) => {
        if (cls.startsWith("cat-")) {
          const catNumber = parseInt(cls.replace("cat-", ""), 10); // Extract the number from the class
          if (!isNaN(catNumber)) {
            catClasses.push(catNumber);
          }
        }
      });
    });

    // Order the classes in ascending order
    catClasses.sort((a, b) => a - b);

    // Check for missing classes and reuse existing ones
    const maxClassNum = Math.max(...catClasses); // Find the maximum class number
    for (let i = 1; i <= maxClassNum; i++) {
      const classToCheck = `cat-${i}`;

      // Check if the class does not exist
      if (!catClasses.includes(i)) {
        // Find the closest existing class to reuse
        const closestClass = catClasses.reverse().find((cls) => cls < i);

        if (closestClass) {
          const categoryToAssign = document.querySelector(
            `.categories .category.cat-${closestClass}`
          );
          if (categoryToAssign) {
            categoryToAssign.classList.add(classToCheck);
            console.log(
              `Reutilizando cat-${closestClass} para ${classToCheck}`
            );
          }
        }
      }
    }

    // Assign the root colors
    catClasses.forEach((catNumber, index) => {
      // Assign the root colors to the categories by using the index
      const className = `.cat-${catNumber}`;

      // Create the before rule
      const beforeRule = `${className}::before { background-color: var(--cat-${
        index + 1
      }-bg); }`;

      // Insert the before rule
      styleSheet.insertRule(beforeRule, styleSheet.cssRules.length);
    });
  }

  assignDynamicStyles();
});
