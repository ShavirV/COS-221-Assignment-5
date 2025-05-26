document.addEventListener("DOMContentLoaded", function () {
  const themeToggle = document.getElementById("theme-toggle");
  const icon = themeToggle.querySelector("i");

  // Check for saved theme preference or use dark mode as default
  const currentTheme = localStorage.getItem("theme") || "dark";

  // Apply the saved theme
  if (currentTheme === "light") {
    document.body.classList.add("light-mode");
    icon.classList.remove("fa-moon");
    icon.classList.add("fa-sun");
  }

  // Theme toggle button click handler
  themeToggle.addEventListener("click", function () {
    document.body.classList.toggle("light-mode");

    if (document.body.classList.contains("light-mode")) {
      localStorage.setItem("theme", "light");
      icon.classList.remove("fa-moon");
      icon.classList.add("fa-sun");
    } else {
      localStorage.setItem("theme", "dark");
      icon.classList.remove("fa-sun");
      icon.classList.add("fa-moon");
    }
  });
});
