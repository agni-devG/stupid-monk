(function () {
    'use strict';

    var products = [
        {
            name: 'Numb',
            description: 'Lorem ipsum dolor sit amet consectetur. Pellentesque orci imperdiet velit nisl amet iaculis quam sollicitudin. Et vehicula.'
        },
        {
            name: 'T-shirt 2',
            description: 'Lorem ipsum dolor sit amet consectetur. Pellentesque orci imperdiet velit nisl amet iaculis quam sollicitudin. Et vehicula.'
        },
        {
            name: 'T-shirt 3',
            description: 'Lorem ipsum dolor sit amet consectetur. Pellentesque orci imperdiet velit nisl amet iaculis quam sollicitudin. Et vehicula.'
        }
    ];
    var stage = document.getElementById('stage');
    var track = document.getElementById('scroll-track');
    var ring = document.getElementById('ring');
    var info = document.getElementById('info');
    var cards = Array.prototype.slice.call(ring.querySelectorAll('.card'));
    var name = document.getElementById('product-name');
    var description = document.getElementById('product-description');
    var addButton = document.getElementById('add-button');
    var buyButton = document.getElementById('buy-button');
    var count = products.length;
    var laps = 3;
    var current = count;
    var target = count;
    var lastDrawn = null;
    var lastIndex = -1;
    var cardWidth;
    var cardHeight;
    var radius;
    var step;
    var notch;
    var resizeTimer;
    var snapTimer;
    var renderFrame;
    var snapFrame;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    history.scrollRestoration = 'manual';

    function wrap(value) {
        return ((value + count / 2) % count + count) % count - count / 2;
    }

    function clamp(value, min, max) {
        return Math.max(min, Math.min(max, value));
    }

    function layout() {
        var viewportWidth = window.innerWidth;
        var viewportHeight = window.innerHeight;
        var mobile = viewportWidth < 760;

        if (mobile) {
            cardWidth = Math.max(170, Math.min(viewportWidth * 0.58, 280, (viewportHeight - 430) / 1.26));
        } else {
            cardWidth = Math.max(
                170,
                Math.min(viewportWidth * 0.175, viewportHeight * (336 / 911))
            );
        }

        cardHeight = cardWidth * 1.26;
        step = mobile ? 27 : 20;
        radius = mobile ? viewportWidth * 0.95 : viewportWidth * 0.635;
        notch = Math.max(320, viewportHeight * 0.5);

        stage.style.setProperty('--card-width', cardWidth + 'px');
        stage.style.setProperty('--card-height', cardHeight + 'px');
        track.style.height = (viewportHeight + count * laps * notch) + 'px';
    }

    function setProduct(index) {
        name.textContent = products[index].name;
        description.textContent = products[index].description;
        addButton.textContent = 'Add to Cart';
        buyButton.textContent = 'Buy Now';
    }

    function render() {
        cards.forEach(function (card, index) {
            var offset = wrap(index - current);
            var distance = Math.abs(offset);

            if (distance > 2.1) {
                card.style.visibility = 'hidden';
                return;
            }

            var angle = offset * step;
            var radians = angle * Math.PI / 180;
            var opacity = distance <= 1 ? 1 : clamp(1 - (distance - 1) / 0.95, 0, 1);
            card.style.transform = 'translate(' + (radius * Math.sin(radians)) + 'px, ' +
                (radius * (1 - Math.cos(radians))) + 'px) rotate(' + angle + 'deg)';
            card.style.opacity = opacity;
            card.style.visibility = opacity > 0.01 ? 'visible' : 'hidden';
            card.style.zIndex = Math.round(100 - distance * 10);
        });

        var nearest = Math.round(current);
        var distanceFromNearest = Math.abs(current - nearest);
        var infoOpacity = clamp(1 - (distanceFromNearest - 0.03) / 0.17, 0, 1);
        info.style.opacity = infoOpacity;
        info.style.visibility = infoOpacity < 0.02 ? 'hidden' : 'visible';
        info.style.pointerEvents = infoOpacity > 0.6 ? 'auto' : 'none';
        info.style.transform = 'translate(-50%, ' + ((1 - infoOpacity) * 10).toFixed(1) + 'px)';

        var productIndex = ((nearest % count) + count) % count;
        if (productIndex !== lastIndex) {
            lastIndex = productIndex;
            setProduct(productIndex);
        }
    }

    function frame() {
        var easing = reducedMotion ? 1 : 1 - Math.pow(1 - 0.12, 16 / 16.67);
        current += (target - current) * easing;
        if (Math.abs(target - current) < 0.0004) {
            current = target;
        }
        if (current !== lastDrawn) {
            lastDrawn = current;
            render();
        }
        renderFrame = window.requestAnimationFrame(frame);
    }

    function updateFromScroll() {
        var progress = window.scrollY / notch;
        var lap = Math.floor(progress / count);

        if (lap !== 1) {
            var shift = (1 - lap) * count;
            var adjustedScroll = window.scrollY + shift * notch;
            current += shift;
            target += shift;
            progress += shift;
            window.scrollTo(0, adjustedScroll);
        }

        target = progress;
        window.clearTimeout(snapTimer);
        snapTimer = window.setTimeout(snapToNearestProduct, 140);
    }

    function snapToNearestProduct() {
        var progress = window.scrollY / notch;
        var nearest = Math.round(progress);
        if (Math.abs(progress - nearest) * notch <= 2) {
            return;
        }

        var start = window.scrollY;
        var destination = nearest * notch;
        var distance = destination - start;
        var duration = reducedMotion ? 0 : 450;
        var startedAt = null;

        function animate(timestamp) {
            if (startedAt === null) {
                startedAt = timestamp;
            }
            var elapsed = timestamp - startedAt;
            var amount = duration === 0 ? 1 : Math.min(elapsed / duration, 1);
            var eased = amount < 0.5 ? 2 * amount * amount : 1 - Math.pow(-2 * amount + 2, 2) / 2;
            window.scrollTo(0, start + distance * eased);
            if (amount < 1) {
                snapFrame = window.requestAnimationFrame(animate);
            }
        }

        window.cancelAnimationFrame(snapFrame);
        snapFrame = window.requestAnimationFrame(animate);
    }

    function cancelSnap() {
        window.clearTimeout(snapTimer);
        window.cancelAnimationFrame(snapFrame);
    }

    window.addEventListener('scroll', updateFromScroll, { passive: true });
    ['wheel', 'touchstart'].forEach(function (eventName) {
        window.addEventListener(eventName, cancelSnap, { passive: true });
    });

    window.addEventListener('keydown', function (event) {
        if (event.target.tagName === 'SELECT') {
            return;
        }
        if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
            event.preventDefault();
            cancelSnap();
            window.scrollBy({ top: notch, behavior: reducedMotion ? 'auto' : 'smooth' });
        } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
            event.preventDefault();
            cancelSnap();
            window.scrollBy({ top: -notch, behavior: reducedMotion ? 'auto' : 'smooth' });
        }
    });

    addButton.addEventListener('click', function () {
        addButton.textContent = 'Added';
        window.setTimeout(function () {
            addButton.textContent = 'Add to Cart';
        }, 1400);
    });

    buyButton.addEventListener('click', function () {
        buyButton.textContent = 'Demo only';
        window.setTimeout(function () {
            buyButton.textContent = 'Buy Now';
        }, 1400);
    });

    window.addEventListener('resize', function () {
        window.clearTimeout(resizeTimer);
        resizeTimer = window.setTimeout(function () {
            var keep = target;
            layout();
            window.scrollTo(0, keep * notch);
            updateFromScroll();
            lastDrawn = null;
        }, 150);
    });

    layout();
    window.scrollTo(0, count * notch);
    current = target = count;
    render();
    renderFrame = window.requestAnimationFrame(frame);
})();
