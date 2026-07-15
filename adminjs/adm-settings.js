/* ==========================================================================
   Settings — profile (name, email, password) and maintenance mode.
   The theme toggle lives only in the sidebar (adm-global.js); this page
   does not duplicate it.
   ========================================================================== */

document.addEventListener("DOMContentLoaded", () => {
    loadSettings();
    bindProfileForm();
    bindMaintenanceForm();
});

function loadSettings() {
    fetch("../configuration/adm-settings-data.php")
        .then(res => res.json())
        .then(data => {
            document.getElementById("profileFirstname").value = data.profile.firstname || "";
            document.getElementById("profileLastname").value = data.profile.lastname || "";
            document.getElementById("profileEmail").value = data.profile.email || "";
            document.getElementById("maintenanceMode").checked = data.settings.maintenanceMode;
        })
        .catch(err => console.error("Failed to load settings:", err));
}

function bindProfileForm() {
    document.getElementById("saveProfileBtn").addEventListener("click", () => {
        const errorEl = document.getElementById("profileFormError");
        const firstname = document.getElementById("profileFirstname").value.trim();
        const lastname = document.getElementById("profileLastname").value.trim();
        const email = document.getElementById("profileEmail").value.trim();
        const password = document.getElementById("profilePassword").value;

        if (!firstname || !lastname || !email) {
            errorEl.textContent = "First name, last name, and email are required.";
            errorEl.classList.add("show");
            return;
        }

        const formData = new FormData();
        formData.append("action", "update_profile");
        formData.append("firstname", firstname);
        formData.append("lastname", lastname);
        formData.append("email", email);
        if (password.trim() !== "") {
            formData.append("password", password);
        }

        fetch("../configuration/adm-settings-actions.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    errorEl.textContent = data.error || "Could not save your profile.";
                    errorEl.classList.add("show");
                    return;
                }
                errorEl.classList.remove("show");
                document.getElementById("profilePassword").value = "";
                flashConfirm("profileSaveConfirm");
            })
            .catch(() => {
                errorEl.textContent = "Network error. Please try again.";
                errorEl.classList.add("show");
            });
    });
}

function bindMaintenanceForm() {
    document.getElementById("saveMaintenanceBtn").addEventListener("click", () => {
        const errorEl = document.getElementById("maintenanceFormError");

        const formData = new FormData();
        formData.append("action", "update_settings");
        if (document.getElementById("maintenanceMode").checked) {
            formData.append("maintenance_mode", "1");
        }

        fetch("../configuration/adm-settings-actions.php", { method: "POST", body: formData })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    errorEl.textContent = data.error || "Could not save this setting.";
                    errorEl.classList.add("show");
                    return;
                }
                errorEl.classList.remove("show");
                flashConfirm("maintenanceSaveConfirm");
            })
            .catch(() => {
                errorEl.textContent = "Network error. Please try again.";
                errorEl.classList.add("show");
            });
    });
}

function flashConfirm(id) {
    const el = document.getElementById(id);
    el.textContent = "Saved.";
    el.classList.add("show");
    setTimeout(() => el.classList.remove("show"), 2000);
}
