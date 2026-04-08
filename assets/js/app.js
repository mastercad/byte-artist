/*
 * byte-artist.de — Main Application JS
 */

require('../css/app.css');

const $ = window.$ = window.jQuery = jQuery = require('jquery');
require('jquery-ui-bundle');
require('lazyload');
require('@fortawesome/fontawesome-free/css/all.min.css');
require('@fortawesome/fontawesome-free/js/all.js');

/* ─── Scroll Reveal ─── */
function initScrollReveal() {
    if (!('IntersectionObserver' in window)) {
        document.querySelectorAll('.animate-on-scroll').forEach(function(el) {
            el.classList.add('is-visible');
        });
        return;
    }
    var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) { e.target.classList.add('is-visible'); obs.unobserve(e.target); }
        });
    }, { threshold: 0.07 });
    document.querySelectorAll('.animate-on-scroll').forEach(function(el) { obs.observe(el); });
}

/* ─── Sticky Nav ─── */
function initNav() {
    var nav = document.querySelector('.site-nav');
    if (!nav) return;
    function update() { nav.classList.toggle('scrolled', window.scrollY > 16); }
    window.addEventListener('scroll', update, { passive: true });
    update();
}

/* ─── Mobile Menu ─── */
function initMobileMenu() {
    var btn   = document.getElementById('nav-toggle');
    var links = document.querySelector('.nav-links');
    if (!btn || !links) return;
    btn.addEventListener('click', function() {
        btn.classList.toggle('open');
        links.classList.toggle('mobile-open');
    });
    links.querySelectorAll('a').forEach(function(a) {
        a.addEventListener('click', function() {
            btn.classList.remove('open');
            links.classList.remove('mobile-open');
        });
    });
}

/* ─── User Dropdown ─── */
function initUserDropdown() {
    var btn = document.querySelector('.nav-user-btn');
    var wrap = document.querySelector('.nav-user');
    if (!btn || !wrap) return;
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        wrap.classList.toggle('open');
    });
    document.addEventListener('click', function() { wrap.classList.remove('open'); });
}

/* ─── Boot ─── */
document.addEventListener('DOMContentLoaded', function() {
    initNav();
    initMobileMenu();
    initUserDropdown();
    initScrollReveal();
});

