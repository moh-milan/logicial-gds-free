    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- JS المخصص -->
    <script src="../assets/js/main.js"></script>
    
    <?php if (isset($custom_scripts)): ?>
        <?php foreach ($custom_scripts as $script): ?>
            <script src="../assets/js/<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    
    </div> <!-- نهاية app-container -->
</body>
</html>