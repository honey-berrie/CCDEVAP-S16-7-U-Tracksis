const API = '../../api';

async function logout() {

  const confirmed = confirm('Are you sure you want to log out?');
  if (!confirmed) return;

  try {
    const res = await fetch(`${API}/auth/logout.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
    });
    const data = await res.json();

    if (!res.ok) {
      alert('Logout failed: ' + (data.error || 'Unknown error'));
      return;
    }

    window.location.href = '../../pages/auth/auth.html';
  } catch (err) {
    alert('Network error: ' + err.message);
  }
  
}