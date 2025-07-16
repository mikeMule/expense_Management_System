</div>

<footer class="footer-react text-center py-4 mt-5">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-center align-items-center gap-2">

            <span class="footer-heart"> Created with <span class="footer-love">❤</span> By <span class="footer-team">Mule wave Team</span></span>
        </div>
    </div>
</footer>

<style>
    .footer-react {
        background: linear-gradient(90deg, #232526 0%, #414345 100%);
        color: #fff;
        font-size: 1.08rem;
        letter-spacing: 0.01em;
        border-top: 2px solid #1976d2;
        box-shadow: 0 -2px 16px rgba(25, 118, 210, 0.08);
    }

    .footer-dev {
        font-weight: 500;
        color: #90caf9;
    }

    .footer-team {
        font-weight: 700;
        color: #42a5f5;
    }

    .footer-heart {
        color: #fff;
        font-weight: 400;
    }

    .footer-love {
        color: #e57373;
        font-size: 1.2em;
        vertical-align: middle;
        animation: pulse 1.2s infinite alternate;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        100% {
            transform: scale(1.18);
        }
    }
</style>

<!-- Bootstrap JS (Unified version 5.3.7 for all pages) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js" integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous"></script>
<!-- Custom JS -->
<script src="assets/js/main.js"></script>

<?php if (isset($additional_scripts)): ?>
    <?php echo $additional_scripts; ?>
<?php endif; ?>
</body>

</html>