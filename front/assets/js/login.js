// login.js
// Rôle : gérer la connexion utilisateur avec redirection vers dashboard

document.addEventListener("DOMContentLoaded", () => {
  const loginForm = document.getElementById("loginForm");

  if (loginForm) {
    loginForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      const formData = new FormData(loginForm);

      try {
        const response = await fetch("/esportify/back/controllers/login.php", {
          method: "POST",
          body: formData
        });

        const text = await response.text();

        if (text.includes("Connexion réussie")) {
          alert("Connexion réussie !");
          window.location.href = "/esportify/front/dashboard.html";
        } else {
          alert(text);
        }
      } catch (err) {
        console.error("Erreur serveur :", err);
        alert("Erreur lors de la connexion.");
      }
    });
  }
});
