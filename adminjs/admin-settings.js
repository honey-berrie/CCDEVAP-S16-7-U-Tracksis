const sectionLabels = {
  general: "General settings",
  notifications: "Notification settings",
  academic: "Academic year settings",
  preferences: "System preferences",
};

function saveSettings(section, btn) {
  const originalText = btn.textContent;
  btn.disabled = true;
  btn.textContent = "Saving...";

  simulateRequest(() => {
    btn.disabled = false;
    btn.textContent = originalText;
    const label = sectionLabels[section] || "Settings";
    showToast(`${label} saved.`, "success");
  }, { delay: 600 });
}
