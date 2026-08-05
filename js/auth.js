const API = "../../api";

// check if user is already logged in
async function checkLoginStatus() {
  try {
    const checkRes = await fetch(`${API}/auth/user.php`, {
      method: "GET",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
    });
    const checkData = await checkRes.json();

    if (checkRes.ok && checkData.user) {
      const role = checkData.user.role;
      if (role === "admin")
        window.location.href = "/admin/dashboard";
      else if (role === "adviser")
        window.location.href =
          "../../phase2-page-based-adviser/adviserhtml/thesis-adviser-overview.php";
      else if (role === "coordinator")
        window.location.href =
          "../../coordinator/coordinator-html/coordinator-overview.php";
      else window.location.href = "../../pages/student/dashboard.html";
      return;
    }
  } catch (err) {
    console.error("Error checking login status:", err);
  }
}

document.addEventListener("DOMContentLoaded", checkLoginStatus);

// === NEW: Helper function to show error modal ===
function showErrorModal(message) {
  const modalBody = document.querySelector("#loginErrorModal .modal-body p");
  if (modalBody) modalBody.textContent = message;
  const errorModal = new bootstrap.Modal(
    document.getElementById("loginErrorModal"),
  );
  errorModal.show();
}

async function login() {
  const email = document.getElementById("login-email").value.trim();
  const password = document.getElementById("login-password").value;

  if (!email || !password) {
    showErrorModal("Please enter your email and password.");
    return;
  }

  try {
    const res = await fetch(`${API}/auth/login.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({ email, password }),
    });
    const data = await res.json();

    if (!res.ok) {
      if (data.maintenance) {
        window.location.href = "../../maintenance.html";
        return;
      }
      showErrorModal(
        data.error ||
          "Login failed. Please check your credentials and try again.",
      );
      return;
    }

    // Show success modal
    const successModal = new bootstrap.Modal(
      document.getElementById("loginSuccessModal"),
    );
    successModal.show();

    const role = data.user.role;
    setTimeout(() => {
      if (role === "admin")
        window.location.href = "/admin/dashboard";
      else if (role === "adviser")
        window.location.href =
          "../../phase2-page-based-adviser/adviserhtml/thesis-adviser-overview.php";
      else window.location.href = "../../pages/student/dashboard.html";
    }, 1500);
  } catch (err) {
    showErrorModal("Network error: " + err.message);
  }
}

async function register() {
  const registerSection = document.getElementById("register-section");
  const loginSection = document.getElementById("login-section");

  function toggleLogin() {
    loginSection.style.display = "block";
    registerSection.style.display = "none";
  }

  const firstname = document.getElementById("firstname").value.trim();
  const lastname = document.getElementById("lastname").value.trim();
  const email = document.getElementById("register-email").value.trim();
  const password = document.getElementById("register-password").value;
  const confirm = document.getElementById("confirm").value;
  const accountType = document.getElementById("accountType").value;

  if (!firstname || !lastname || !email || !password || !confirm) {
    alert("Please fill in all fields.");
    return;
  }
  if (password.length < 8) {
    alert("Password must be at least 8 characters.");
    return;
  }
  if (password !== confirm) {
    alert("Passwords do not match.");
    return;
  }

  try {
    const res = await fetch(`${API}/auth/signup.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      credentials: "include",
      body: JSON.stringify({
        firstname,
        lastname,
        email,
        password,
        confirm,
        accountType,
      }),
    });
    const data = await res.json();

    if (!res.ok) {
      alert(data.error || "Registration failed");
      return;
    }

    alert("Registration successful!");
    toggleLogin();
  } catch (err) {
    alert("Network error: " + err.message);
  }
}
