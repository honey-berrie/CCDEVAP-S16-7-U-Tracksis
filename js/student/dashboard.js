const API = '../../api';


async function fetchUserData() {
  const userNameElement = document.getElementById('topbar-user');

  try {

    const res = await fetch(`${API}/auth/user.php`, {
      method: 'GET',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'include',
    });
    const data = await res.json();

    if (!res.ok) {
      alert('Failed to fetch user data: ' + res.statusText);
      window.location.href = '../../pages/auth/auth.html';
      return;
    }

    const user = data.user;
    if (user && user.firstname && user.lastname) {
      userNameElement.textContent = `${user.firstname} ${user.lastname}`;
    }

  } catch (err) {
    alert('Network error: ' + err.message);
  }
}

fetchUserData();

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