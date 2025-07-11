// main.js
// ➤ Rôle : interactions globales (menu, modale connexion, vérification inscription, top10 modal)

document.addEventListener("DOMContentLoaded", () => {
  console.log("E.SPORTIFY chargé !");
});

// ➤ MENU BURGER : toggle sur clic
function toggleMenu() {
  const nav = document.querySelector('.main-nav');
  nav.classList.toggle('show');
}

// ➤ Ouvre la modale de connexion
function openModal() {
  document.getElementById("authModal").style.display = "block";
}

// ➤ Ferme la modale de connexion
function closeModal() {
  document.getElementById("authModal").style.display = "none";
}

// ➤ Gestion des onglets Connexion/Inscription
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

// ➤ Vérifie les mots de passe à l'inscription
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

// ➤ Gère l’envoi du formulaire d’inscription
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

// ➤ Ouvre le modal Top 10 (♛)
function openTop10Modal() {
  const modal = document.getElementById("top10Modal");
  const content = document.getElementById("top10Content");

  if (!modal || !content) return;

  modal.style.display = "flex";
  content.innerHTML = "Chargement...";

  fetch("/esportify/back/pages/top10.php")
    .then(res => res.text())
    .then(html => {
      content.innerHTML = html;
    })
    .catch(err => {
      content.innerHTML = "<p>Erreur de chargement du classement.</p>";
      console.error("Erreur top10 :", err);
    });
}

// ➤ Ferme le modal Top 10
function closeTop10Modal() {
  const modal = document.getElementById("top10Modal");
  if (modal) modal.style.display = "none";
}
