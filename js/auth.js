const loginEmail = document.getElementById('login-email');
const loginPassword = document.getElementById('login-password');

function login() {
    const email = loginEmail.value.trim();
    const password = loginPassword.value.trim();

    console.log('Login attempt with email:', email);

    if (!email || !password) {
        alert('Please enter both email and password.');
        return;
    }

    if (email === 'student@example.com' && password === 'student123') {
        window.location.href = '../../pages/student/dashboard.html';
    } else if (email === 'admin@example.com' && password === 'admin123') {
        // redirect to admin dashboard

        alert('(Admin Dashboard redirection not implemented yet)'); // remove after implementing

    } else if (email === 'coordinator@example.com' && password === 'coordinator123') {
        // redirect to coordinator dashboard

        alert('(Coordinator Dashboard redirection not implemented yet)'); // remove after implementing

    } else if (email === 'adviser@example.com' && password === 'adviser123') {
        // redirect to adviser dashboard

        alert('(Adviser Dashboard redirection not implemented yet)'); // remove after implementing

    } else {
        alert('Invalid email or password.');
    }
}