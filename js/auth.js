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

        window.location.href = '../../adminhtml/admin-dashboard.html';


    } else if (email === 'coordinator@example.com' && password === 'coordinator123') {
        // redirect to coordinator dashboard

        window.location.href = '../../coordinator/coordinator-html/coordinator-overview.html';

    } else if (email === 'adviser@example.com' && password === 'adviser123') {
        // redirect to adviser dashboard

        window.location.href = '../../adviserhtml/thesis-adviser-overview.html';

    } else {
        alert('Invalid email or password.');
    }
}