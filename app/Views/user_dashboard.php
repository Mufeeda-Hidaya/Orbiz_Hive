<?php include "common/header.php"; ?>

<style>
    .welcome-container {
        display: flex;
        justify-content: center; /* Horizontal center */
        align-items: flex-start; /* Align to top */
        padding: 40px 20px; /* Top padding so it’s not glued to the edge */
    }
    .welcome-card {
        background: linear-gradient(90deg, #7b2ff7, #f107a3);
        color: #fff;
        border-radius: 15px;
        padding: 73px;
        text-align: center;
        max-width: 680px;
        width: 100%;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    .welcome-card img {
        width: 80px;
        margin-bottom: 15px;
    }
    .welcome-card h2 {
        font-weight: bold;
        font-size: 28px;
        margin-bottom: 10px;
    }
    .welcome-card p {
        font-size: 16px;
        margin: 0;
    }
</style>
<div class="right_container">
    <div class="welcome-container">
        <div class="welcome-card">
            <?php
                $session = session();
                $displayName = $session->get('role_Name') === 'admin'
                    ? ucfirst($session->get('role_Name'))
                    : ucfirst($session->get('user_Name'));
            ?>
            <h2>Welcome Back, <?= $displayName ?>! 🎉</h2>
            <p>We're thrilled to have you here. Explore your dashboard and manage your tasks efficiently.</p>
        </div>
    </div>
</div>
</div>
<?php include "common/footer.php"; ?>
