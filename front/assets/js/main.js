// main.js
// Rôle : gérer les interactions UI générales (menu burger, modale, tabs, vérification inscription)

document.addEventListener("DOMContentLoaded", () => {
  console.log("E.SPORTIFY chargé !");
});

// MENU BURGER : affiche/masque le menu principal
function toggleMenu() {
  const nav = document.querySelector('.main-nav');
  nav.classList.toggle('show');
}

// Affiche la modale de connexion/inscription
function openModal() {
  document.getElementById("authModal").style.display = "block";
}

// Ferme la modale
function closeModal() {
  document.getElementById("authModal").style.display = "none";
}

// Bascule entre les onglets "Connexion" et "Inscription"
function showTab(tab) {
  const loginForm = document.getElementById("loginForm");
  const registerForm = document.getElementById("registerForm");
  const loginTab = document.getElementById("loginTab");
  const registerTab = document.getElementById("registerTab");

  if (tab === "login") {
    loginForm.classList.remove("hidden");
    registerForm.classList.add("hidden");
    loginTab.classList.add("active");
    registerTab.classList.remove("active");
  } else {
    loginForm.classList.add("hidden");
    registerForm.classList.remove("hidden");
    loginTab.classList.remove("active");
    registerTab.classList.add("active");
  }
}

// Vérifie si les deux mots de passe correspondent à l'inscription
function checkPasswordsMatch() {
  const password = document.getElementById("password").value;
  const confirm = document.getElementById("confirm_password").value;
  const error = document.getElementById("passwordError");

  if (password !== confirm) {
    error.style.display = "block";
    return false;
  }

  error.style.display = "none";
  return true;
}

// Gère le formulaire d'inscription
document.addEventListener("DOMContentLoaded", () => {
  const registerForm = document.getElementById("registerForm");

  if (registerForm) {
    registerForm.addEventListener("submit", async function (e) {
      e.preventDefault();

      if (!checkPasswordsMatch()) return;

      const formData = new FormData(registerForm);

      try {
        const response = await fetch("/esportify/back/controllers/register.php", {
          method: "POST",
          body: formData
        });

        const text = await response.text();

        if (text.includes("réussie")) {
          alert("Inscription réussie !");
          registerForm.reset();
        } else {
          alert(text);
        }
      } catch (err) {
        console.error("Erreur serveur :", err);
        alert("Erreur lors de l’inscription.");
      }
    });
  }
});
