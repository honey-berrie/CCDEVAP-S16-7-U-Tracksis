// Toggle between login and register sections
const registerSection = document.getElementById("register-section");
const loginSection = document.getElementById("login-section");

loginSection.querySelector("a").addEventListener("click", (e) => {
    e.preventDefault();
    loginSection.style.display = "none";
    registerSection.style.display = "block";
    clearPrompts();
});

registerSection.querySelector("a").addEventListener("click", (e) => {
    e.preventDefault();
    registerSection.style.display = "none";
    loginSection.style.display = "block";
    clearPrompts();
});

function clearPrompts(){
    let prompts = document.querySelectorAll('.error-msg');
    
    prompts.forEach(e => {
        e.remove();
    })
}

//  Role selection logic
const roleButtons = document.querySelectorAll(".btn-role");
const hiddenInput = document.getElementById("accountType");

roleButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
        roleButtons.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
        hiddenInput.value = btn.dataset.role;
    });
});