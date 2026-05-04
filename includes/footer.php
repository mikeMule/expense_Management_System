        </div> <!-- End of main-content -->
    </div> <!-- End of wrapper flex -->

    <footer class="bg-gradient-to-r from-gray-800 to-gray-900 text-white text-center py-6 mt-10 border-t-4 border-brand shadow-inner">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-center items-center gap-2 text-lg tracking-wide">
                <span class="font-normal text-gray-300">
                    Created with <span class="text-red-400 text-xl align-middle inline-block animate-pulse">❤</span> By <span class="font-bold text-blue-400">Mule wave Team</span>
                </span>
            </div>
        </div>
    </footer>

<!-- JQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<!-- Note: Removed DataTables Bootstrap 5 JS and Core Bootstrap JS as we are migrating to Tailwind -->
<!-- Custom JS -->
<script src="<?php echo AssetManager::url('assets/js/main.js'); ?>"></script>

<?php if (isset($additional_scripts)): ?>
    <?php echo $additional_scripts; ?>
<?php endif; ?>
</body>

</html>