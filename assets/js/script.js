document.addEventListener("DOMContentLoaded", function () {
  const nodes = document.querySelectorAll(".tree li.node");

  // Function to apply filters
  function applyFilters(category) {
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

  applyFilters("all");

  // Event listener for category filters
  const categories = document.querySelectorAll(".categories .category");

  categories.forEach((category) => {
    // mouseenter and mouseleave events for highlighting
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

    // click event for category filtering
    category.addEventListener("click", () => {
      const selectedCategoryId = category.id;

      if (category.classList.contains("active")) {
        applyFilters("all");
      } else {
        categories.forEach((item) => item.classList.remove("active"));
        category.classList.add("active");
        applyFilters(selectedCategoryId);

        const selectedCategory = document.getElementById(selectedCategoryId);
        selectedCategory.scrollIntoView({ behavior: "smooth" });
        window.scrollTo({ top: 0, behavior: "smooth" });
      }
    });
  });

  // Assign colors to categories and nodes dynamically
  function assignDynamicStyles() {
    const categories = document.querySelectorAll('[class*="cat-"]');
    const root = document.documentElement;
    const style = document.createElement("style");
    document.head.appendChild(style);
    const styleSheet = style.sheet;

    // Save all "cat-" class numbers
    const catClasses = new Set();

    categories.forEach((category) => {
      category.classList.forEach((cls) => {
        if (cls.startsWith("cat-")) {
          const catNumber = parseInt(cls.replace("cat-", ""), 10);
          if (!isNaN(catNumber)) {
            catClasses.add(catNumber);
          }
        }
      });
    });

    // Convert to array and sort the classes
    const sortedCatClasses = Array.from(catClasses).sort((a, b) => a - b);

    // Assign colors and fill missing categories
    sortedCatClasses.forEach((catNumber, index) => {
      const className = `.cat-${catNumber}`;
      const beforeRule = `${className}::before { background-color: var(--cat-${
        (index % 24) + 1 // Cycle through the first 24 colors
      }-bg); }`;

      styleSheet.insertRule(beforeRule, styleSheet.cssRules.length);
    });

    // Fill missing classes by reusing closest colors
    const maxClassNum = Math.max(...sortedCatClasses);
    for (let i = 1; i <= maxClassNum; i++) {
      if (!catClasses.has(i)) {
        const closestClass = sortedCatClasses
          .slice()
          .reverse()
          .find((cls) => cls < i);

        if (closestClass) {
          const categoryToAssign = document.querySelector(
            `.categories .category.cat-${closestClass}`
          );
          if (categoryToAssign) {
            categoryToAssign.classList.add(`cat-${i}`);
            console.log(`Reutilizando cat-${closestClass} para cat-${i}`);
          }
        }
      }
    }
  }

  assignDynamicStyles();
});
