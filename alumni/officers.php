<?php
// Header and session
include 'includes/header.php';
?>

<?php include 'includes/navbar.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<main class="main-content lg:ml-72 pt-16 min-h-screen">
	<div class="p-6">
		<div class="mb-6">
			<h1 class="text-3xl font-bold text-gray-900">Board of Officers</h1>
			<p class="text-gray-600">Meet the officers serving the SLSU-HC Alumni community.</p>
		</div>

		<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
			<!-- Placeholder cards; replace with dynamic content if available -->
			<?php for ($i = 0; $i < 4; $i++): ?>
			<div class="bg-white rounded-xl shadow-md border border-gray-100 p-6">
				<div class="flex items-center space-x-4">
					<div class="w-14 h-14 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-semibold">
						<i class="fas fa-user-tie"></i>
					</div>
					<div>
						<p class="text-gray-900 font-semibold">Officer Name</p>
						<p class="text-gray-500 text-sm">Position Title</p>
					</div>
				</div>
				<div class="mt-4 text-sm text-gray-600">
					<p>Email: example@slsu-hc.edu.ph</p>
				</div>
			</div>
			<?php endfor; ?>
		</div>
	</div>
</main>

<?php include 'includes/footer.php'; ?>

