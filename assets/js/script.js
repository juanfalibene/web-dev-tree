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
    const categories = document.querySelectorAll('[class*="cat-"]'); // Selecciona todos los elementos con la clase "cat-"
    const root = document.documentElement; // Referencia a :root para acceder a las variables CSS

    // Crear un nuevo elemento de estilo
    const style = document.createElement("style");
    document.head.appendChild(style);

    // Obtener el objeto de la hoja de estilos
    const styleSheet = style.sheet;

    // Almacena las clases que tienen el formato "cat-X"
    const catClasses = [];

    categories.forEach((category) => {
      // Filtra las clases que sigan el patrón "cat-X"
      category.classList.forEach((cls) => {
        if (cls.startsWith("cat-")) {
          const catNumber = parseInt(cls.replace("cat-", ""), 10); // Extrae el número de la clase
          if (!isNaN(catNumber)) {
            catClasses.push(catNumber);
          }
        }
      });
    });

    // Ordena las clases por número
    catClasses.sort((a, b) => a - b);

    // Asigna las variables CSS a las clases correspondientes
    catClasses.forEach((catNumber, index) => {
      // Aquí se hace la asignación de las variables de color según el orden encontrado
      const className = `.cat-${catNumber}`;

      // Crea reglas CSS individuales para categorías
      const beforeRule = `${className}::before { background-color: var(--cat-${
        index + 1
      }-bg); }`;

      // Inserta la regla de fondo
      styleSheet.insertRule(beforeRule, styleSheet.cssRules.length);
    });
  }

  assignDynamicStyles();
});
