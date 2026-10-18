<?php
/**
 * Tiano Hotel & Suites - Modular Top Header Bar
 */
$hotel_phone   = get_setting($pdo, 'hotel_phone', '+234 814 473 4612');
$hotel_email   = get_setting($pdo, 'hotel_email', 'tianohotel@gmail.com');
$hotel_address = get_setting($pdo, 'hotel_address', '44 Marina Road, Eket, Akwa Ibom State, Nigeria');
?>
<!-- Top Header -->
<header class="top-header top-header-bg">
    <div class="container">
        <div class="row align-items-center">
            
            <!-- Language Selection Column -->
            <div class="col-lg-3 col-md-2 pr-0">
                <div class="language-list">
                    <select class="language-list-item">
                        <option>English</option>
                        <option>العربيّة</option>
                        <option>Deutsch</option>
                        <option>Português</option>
                        <option>简体中文</option>
                    </select>
                </div>
            </div>
            
            <!-- Contact Information Column -->
            <div class="col-lg-9 col-md-10">
                <div class="header-right">
                    <ul>
                        <!-- Address -->
                        <li>
                            <i class="bx bx-home-alt"></i>
                            <a href="https://maps.google.com/?q=44+Marina+Road+Eket" target="_blank" rel="noopener">
                                <?php echo htmlspecialchars($hotel_address); ?>
                            </a>
                        </li>
                        
                        <!-- Phone Number -->
                        <li>
                            <i class="bx bx-phone-call"></i>
                            <a href="tel:<?php echo preg_replace('/[^0-9+]/', '', $hotel_phone); ?>">
                                <?php echo htmlspecialchars($hotel_phone); ?>
                            </a>
                        </li>
                        
                        <!-- Email -->
                        <li>
                            <i class="bx bx-envelope"></i>
                            <a href="mailto:<?php echo htmlspecialchars($hotel_email); ?>">
                                <?php echo htmlspecialchars($hotel_email); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
        </div>
    </div>
</header>
