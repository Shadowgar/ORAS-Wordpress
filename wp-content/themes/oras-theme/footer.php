<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<?php astra_content_bottom(); ?>
	</div> <!-- ast-container -->
	</div><!-- #content -->
<?php
	astra_content_after();

	astra_footer_before();

	astra_footer();

	astra_footer_after();
?>
	</div><!-- #page -->
<div id="oras-dark-mode-toggle" class="wp-dark-mode-switch-styled wp-dark-mode-switch-3">
    <div class="_track">
        <div class="_icon">
            <!-- Moon icon -->
            <svg viewBox="0 0 15 15" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M7.39113 2.94568C7.21273 2.94568 7.06816 2.80111 7.06816 2.62271V0.322968C7.06816 0.144567 7.21273 0 7.39113 0C7.56953 0 7.7141 0.144567 7.7141 0.322968V2.62271C7.7141 2.80111 7.56953 2.94568 7.39113 2.94568ZM7.39105 11.5484C6.84467 11.5484 6.31449 11.4414 5.81517 11.2302C5.33308 11.0262 4.9002 10.7344 4.52843 10.3628C4.15665 9.99108 3.86485 9.5582 3.66103 9.07611C3.44981 8.57679 3.34277 8.04661 3.34277 7.50023C3.34277 6.95385 3.44981 6.42367 3.66103 5.92435C3.86496 5.44225 4.15676 5.00937 4.52843 4.6377C4.9001 4.26603 5.33298 3.97413 5.81507 3.7703C6.31439 3.55909 6.84457 3.45205 7.39095 3.45205C7.93733 3.45205 8.46751 3.55909 8.96683 3.7703C9.44893 3.97423 9.88181 4.26603 10.2535 4.6377C10.6251 5.00937 10.917 5.44225 11.1209 5.92435C11.3321 6.42367 11.4391 6.95385 11.4391 7.50023C11.4391 8.04661 11.3321 8.57679 11.1209 9.07611C10.9169 9.5582 10.6251 9.99108 10.2535 10.3628C9.88181 10.7344 9.44893 11.0263 8.96683 11.2302C8.46761 11.4414 7.93743 11.5484 7.39105 11.5484Z"/>
            </svg>
        </div>
        <span class="_thumb"></span>
        <div class="_icon">
            <!-- Sun icon -->
            <svg viewBox="0 0 25 25" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M23.3773 16.5026C22.0299 17.0648 20.5512 17.3753 19 17.3753C12.7178 17.3753 7.625 12.2826 7.625 6.00031C7.625 4.44912 7.9355 2.97044 8.49773 1.62305C4.38827 3.33782 1.5 7.39427 1.5 12.1253C1.5 18.4076 6.59276 23.5003 12.875 23.5003C17.606 23.5003 21.6625 20.612 23.3773 16.5026Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {
    const canvas = document.getElementById('site-star-canvas');
    const ctx = canvas.getContext('2d');

    function resizeCanvas() {
        const dpr = window.devicePixelRatio || 1;
        canvas.width = window.innerWidth * dpr;
        canvas.height = window.innerHeight * dpr;
        canvas.style.width = window.innerWidth + "px";
        canvas.style.height = window.innerHeight + "px";
        ctx.setTransform(1,0,0,1,0,0);
        ctx.scale(dpr, dpr);
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    const stars = [];
    const totalStars = 1500; // dense star field
    const colorChance = 0.02; // 2% colored stars
    const colors = ['#ffffff', '#ffffff', '#ffffff', '#ffffff', '#ffffff', '#4a90e2', '#ff4500', '#00cc44', '#ffd700', '#ff8c00'];

    for (let i = 0; i < totalStars; i++) {
        let isColored = Math.random() < colorChance;
        stars.push({
            x: Math.random() * window.innerWidth,
            y: Math.random() * window.innerHeight,
            radius: Math.random() * 1.0 + 0.2, // smaller stars
            color: isColored ? colors[Math.floor(Math.random() * colors.length)] : '#ffffff',
            baseOpacity: Math.random() * 0.4 + 0.2,
            twinkleSpeed: Math.random() * 1 + 0.5, // slow subtle twinkle
            phase: Math.random() * Math.PI * 2,
            depth: Math.random() * 3 + 1
        });
    }

    function drawStars(scrollOffset, time) {
        ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
        stars.forEach(star => {
            const yOffset = star.y + scrollOffset / (star.depth * 12); // slower parallax
            const opacity = star.baseOpacity + Math.sin(time * star.twinkleSpeed + star.phase) * 0.15;
            const clampedOpacity = Math.max(0.1, Math.min(opacity, 0.9));

            ctx.beginPath();
            ctx.arc(star.x, yOffset % window.innerHeight, star.radius, 0, Math.PI * 2);
            ctx.fillStyle = `rgba(${hexToRgb(star.color)}, ${clampedOpacity})`;
            ctx.fill();
        });
    }

    function animate(time) {
        const scrollOffset = window.scrollY;
        drawStars(scrollOffset, time * 0.001);
        requestAnimationFrame(animate);
    }

    animate(0);

    function hexToRgb(hex) {
        hex = hex.replace('#','');
        let bigint = parseInt(hex,16);
        let r = (bigint >> 16) & 255;
        let g = (bigint >> 8) & 255;
        let b = bigint & 255;
        return `${r},${g},${b}`;
    }
});
	
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.getElementById('oras-dark-mode-toggle');

    function applyMode(isLight) {
        // Body class
        document.body.classList.toggle('light-mode', isLight);

        // Starfield
        const starfield = document.querySelectorAll('#nebula-canvas, #star-canvas');
        starfield.forEach(el => el.style.display = isLight ? 'none' : 'block');

        // Body background & text
        document.body.style.background = isLight ? '#ffffff' : '#01010a';
        document.body.style.color = isLight ? '#000000' : '#ffffff';

        // Elementor Membership Portal
        const portals = document.querySelectorAll('.oras-membership-portal');
        portals.forEach(box => {
            box.style.backgroundColor = isLight ? '#ffffff' : '#000000';
            box.style.color = isLight ? '#000000' : '#ffffff';
            box.querySelectorAll('*').forEach(el => {
                el.style.color = isLight ? '#000000' : '#ffffff';
                if(el.tagName === 'A') el.style.color = isLight ? '#0073e6' : '#1e90ff';
                if(el.tagName === 'P' || el.tagName === 'DIV') el.style.backgroundColor = isLight ? '#ffffff' : '#000000';
            });
        });

        // WooCommerce & PMPro
        const wcPmSelectors = [
            '.woocommerce-page',
            '.woocommerce-cart table.shop_table',
            '.woocommerce-cart .cart-collaterals .cart_totals',
            '.woocommerce-checkout .shop_table',
            '.woocommerce-checkout input',
            '.woocommerce-checkout select',
            '.woocommerce-checkout textarea',
            '.pmpro_form',
            '#pmpro_account',
            '#pmpro_checkout_box',
            '#pmpro_level-boxes',
            '.pmpro_login_wrap',
            '#pmpro_account table',
            '.pmpro_table'
        ];
        wcPmSelectors.forEach(sel => {
            document.querySelectorAll(sel).forEach(el => {
                if(isLight) {
                    el.style.backgroundColor = (el.tagName === 'TABLE') ? '#f5f5f5' : '#ffffff';
                    el.style.color = '#000000';
                    if(el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') el.style.borderColor = '#ccc';
                } else {
                    if(sel.includes('table')) {
                        el.style.backgroundColor = (sel.includes('pmpro')) ? '#1a1a1a' : '#222222';
                    } else {
                        el.style.backgroundColor = (sel.includes('pmpro')) ? '#111' : '#222222';
                    }
                    el.style.color = '#ffffff';
                    if(el.tagName === 'INPUT' || el.tagName === 'SELECT' || el.tagName === 'TEXTAREA') el.style.borderColor = '#444';
                }
            });
        });

        // Header
        const header = document.querySelector('.elementor-location-header');
        if(header) {
            if(isLight) {
                header.style.backgroundColor = 'rgba(0,0,0,0.85)';
                header.querySelectorAll('*').forEach(el => {
                    el.style.color = '#ffffff';
                });
            } else {
                if(!header.classList.contains('elementor-sticky--effects')) {
                    header.style.backgroundColor = 'transparent';
                }
                header.querySelectorAll('*').forEach(el => {
                    el.style.color = ''; // reset to theme default
                });
            }
        }

        // Toggle button style
        toggle.classList.toggle('light', isLight);

        // Save preference
        localStorage.setItem('oras-light-mode', isLight ? '1' : '0');
    }

    // Load saved mode
    const saved = localStorage.getItem('oras-light-mode');
    if(saved === '1') applyMode(true);

    // Toggle click
    toggle.addEventListener('click', function () {
        applyMode(!document.body.classList.contains('light-mode'));
    });
});

		
</script>













<?php
	astra_body_bottom();
	wp_footer();
?>
	</body>
</html>