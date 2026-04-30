<?php
$pageTitle = 'About Us';
require_once '../../includes/header.php';
?>

<div class="container py-5">
    <!-- Hero About Section -->
    <div class="row align-items-center mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div style="background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 100%); border-radius:20px; padding:20px; text-align:center;">
                <img src="../../assets/images/about/petalora-shop.jpg" alt="Petalora Team" style="width:100%; max-width:600px; border-radius:16px; box-shadow:0 10px 30px rgba(0,0,0,0.15);">
            </div>
        </div>
        <div class="col-lg-6">
            <h2 style="font-family:'Playfair Display',serif; color:var(--primary-dark); margin-bottom:20px;">About Petalora</h2>
            <p style="font-size:1.1rem; line-height:1.8;">Petalora is a small florist shop in Melaka that has been part of the local community since 1990. We create simple, beautiful, and affordable flower bouquets for all kinds of occasions — whether it’s a birthday, wedding, or just a little surprise for someone special</p>
            <p style="font-size:1.1rem; line-height:1.8;">Being located in a quieter kampung area, Petalora faced challenges in reaching more customers over the years. As more people started shopping online, it became clear that we needed a better way to connect with a wider audience. That’s when we decided to take a step forward. Petalora worked together with three Diploma in Information Technology students from Multimedia University (MMU) to build our own online florist system.</p>
            <p style="font-size:1.1rem; line-height:1.8;">Now, customers can easily browse and order bouquets anytime, from anywhere. Today, Petalora blends traditional floral craftsmanship with modern technology — while still keeping the same heart and personal touch we’ve always had. We’re also truly grateful to Multimedia University for nurturing talented students who helped bring this idea to life.</p>
        </div>
    </div>
    
    <!-- Features -->
    <div class="row text-center mb-5">
        <div class="col-md-4 mb-4">
            <div style="width:80px; height:80px; background:rgba(233,30,99,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 15px;">
                <i class="fas fa-heart" style="color:var(--primary); font-size:2rem;"></i>
            </div>
            <h5>Made with Love</h5>
            <p class="text-muted">Every bouquet is handcrafted with passion and care by our skilled florists.</p>
        </div>
        <div class="col-md-4 mb-4">
            <div style="width:80px; height:80px; background:rgba(76,175,80,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 15px;">
                <i class="fas fa-leaf" style="color:var(--secondary); font-size:2rem;"></i>
            </div>
            <h5>Fresh Guaranteed</h5>
            <p class="text-muted">We source fresh flowers daily from trusted local growers.</p>
        </div>
        <div class="col-md-4 mb-4">
            <div style="width:80px; height:80px; background:rgba(255,152,0,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 15px;">
                <i class="fas fa-shipping-fast" style="color:var(--accent); font-size:2rem;"></i>
            </div>
            <h5>Fast Delivery</h5>
            <p class="text-muted">Same-day delivery available for orders placed before 3 PM in Melaka area.</p>
        </div>
    </div>
    
    <!-- Team Section -->
    <div class="text-center mb-5">
        <h2 style="font-family:'Playfair Display',serif; color:var(--primary-dark); margin-bottom:30px;">Meet Our Developers</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div style="background:white; border-radius:20px; padding:40px; box-shadow:0 4px 15px rgba(0,0,0,0.1);">
                    <img src="../../assets/images/about/team.jpg" alt="Petalora Team" style="width:100%; max-width:600px; border-radius:16px; margin-bottom:25px;">
                    <h4 style="color:var(--primary-dark);">The Minds Behind Petalora</h4>
                    <p class="text-muted">Aida Syamilah Muhamad Ali | Wong Ruo Xuan | Lim Hui Shan </p>
                    <p class="text-muted">"We are a team of passionate Diploma in Information Technology students from Multimedia University (MMU), brought together by a shared goal—to help local businesses grow through technology.

Petalora is more than just a project to us. It represents our effort to combine creativity, problem-solving, and technical skills to build something meaningful for a real business. From designing the user interface to developing the system and managing the database, every part of this platform reflects our teamwork and dedication.

Through this project, we aim to make flower shopping easier, faster, and more accessible—while supporting Petalora in reaching more customers beyond its local community."</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../../includes/footer.php'; ?>
