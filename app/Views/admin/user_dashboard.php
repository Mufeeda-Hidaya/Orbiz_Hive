<div class="right_container">
    <div class="welcome-container">
        <div class="welcome-card-color">
            <?php
            $session = session();
            $displayName = $session->get('role_name') === 'admin'
                ? ucfirst($session->get('role_name'))
                : ucfirst($session->get('user_name')); 
            ?>
            
            <h2>Welcome Back, <?= $displayName ?>! 🎉</h2>
            <p>We're thrilled to have you here. Explore your dashboard and manage your tasks efficiently.</p>
        </div>
    </div>
</div>
</div>