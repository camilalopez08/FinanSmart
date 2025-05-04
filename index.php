<?php
// Incluir archivo de encabezado
include_once "includes/header.php";
?>

<main>
    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-success to-info text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="display-4 fw-bold mb-3">Controla tus finanzas, construye tu futuro</h1>
                    <p class="lead mb-4">
                        FinanSmart te ayuda a gestionar tus ingresos y gastos, establecer metas de ahorro y visualizar tu progreso financiero de manera sencilla.
                    </p>
                    <a href="register.php" class="btn btn-light btn-lg px-4 me-md-2">
                        Comenzar ahora <i class="fas fa-chevron-right ms-2"></i>
                    </a>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow-lg">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title mb-0">Resumen Financiero</h5>
                                <span class="text-muted small">Ejemplo</span>
                            </div>
                            <div class="bg-light p-3 rounded mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Ingresos</span>
                                    <span class="text-success fw-bold">$2,500.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Gastos</span>
                                    <span class="text-danger fw-bold">$1,850.00</span>
                                </div>
                                <div class="d-flex justify-content-between pt-2 border-top">
                                    <span class="fw-bold">Balance</span>
                                    <span class="text-success fw-bold">$650.00</span>
                                </div>
                            </div>
                            <div class="text-center py-4">
                                <i class="fas fa-chart-pie fa-5x text-success opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Características Principales</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                                <i class="fas fa-exchange-alt text-success"></i>
                            </div>
                            <h3 class="h5 card-title">Gestión de Transacciones</h3>
                            <p class="card-text text-muted">
                                Registra tus ingresos y gastos de forma sencilla, categorizándolos para un mejor control.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                                <i class="fas fa-bullseye text-success"></i>
                            </div>
                            <h3 class="h5 card-title">Metas de Ahorro</h3>
                            <p class="card-text text-muted">
                                Establece objetivos financieros y haz seguimiento de tu progreso para alcanzarlos.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="bg-success bg-opacity-10 p-3 rounded-circle d-inline-flex mb-3">
                                <i class="fas fa-chart-bar text-success"></i>
                            </div>
                            <h3 class="h5 card-title">Reportes Visuales</h3>
                            <p class="card-text text-muted">
                                Visualiza tus finanzas con gráficos interactivos que te ayudan a entender tus hábitos financieros.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-5 bg-success text-white">
        <div class="container text-center">
            <h2 class="fw-bold mb-3">Comienza a controlar tus finanzas hoy</h2>
            <p class="lead mb-4 mx-auto" style="max-width: 700px;">
                Únete a miles de personas que ya están mejorando su salud financiera con FinanSmart.
            </p>
            <a href="register.php" class="btn btn-light btn-lg">
                Crear cuenta gratuita
            </a>
        </div>
    </section>
</main>

<?php
// Incluir archivo de pie de página
include_once "includes/footer.php";
?>
