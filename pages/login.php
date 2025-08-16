<?php include 'includes/header.php'; ?>

<!-- Page de connexion -->
<div class="login-container">
    <div class="login-box">
        <div class="logo">GP</div>
        <h2 class="login-title">Connexion Gestionnaire</h2>
        
        <div id="error-message" class="error-message" style="display: none;"></div>
        
        <form id="loginForm">
            <div class="form-group">
                <label for="username">Nom d'utilisateur</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-primary">Se connecter</button>
        </form>
        
        <div class="client-link">
            <p>Vous êtes client ? <a href="?page=client">Suivre votre colis</a></p>
        </div>
    </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('error-message');
    
    try {
        const response = await fetch('?page=api&action=login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username, password })
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.href = '?page=dashboard';
        } else {
            errorDiv.textContent = result.message || 'Erreur de connexion';
            errorDiv.style.display = 'block';
        }
    } catch (error) {
        errorDiv.textContent = 'Erreur de connexion';
        errorDiv.style.display = 'block';
    }
});
</script>

<style>
.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #f5c6cb;
}
</style>

<?php include 'includes/footer.php'; ?>
