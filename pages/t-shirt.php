<!DOCTYPE html>
<html lang="en">

<?php include __DIR__ . '/../templates/head.php'; ?>

<body class="t-shirt-page">
    <?php include __DIR__ . '/../templates/nav.php'; ?>

    <?php
    $products = [
        [
            'name' => 'Numb',
            'image' => 'Numb.png',
            'alt' => 'Numb T-shirt',
            'description' => 'Lorem ipsum dolor sit amet consectetur. Pellentesque orci imperdiet velit nisl amet iaculis quam sollicitudin. Et vehicula.',
        ],
        [
            'name' => 'T-shirt 2',
            'image' => 'T-shirt-2.png',
            'alt' => 'T-shirt design 2',
            'description' => 'Lorem ipsum dolor sit amet consectetur. Pellentesque orci imperdiet velit nisl amet iaculis quam sollicitudin. Et vehicula.',
        ],
        [
            'name' => 'T-shirt 3',
            'image' => 'T-shirt-3.png',
            'alt' => 'T-shirt design 3',
            'description' => 'Lorem ipsum dolor sit amet consectetur. Pellentesque orci imperdiet velit nisl amet iaculis quam sollicitudin. Et vehicula.',
        ],
        [
            'name' => 'T-shirt 4',
            'image' => 'T-shirt-3.png',
            'alt' => 'T-shirt design 3',
            'description' => 'Lorem ipsum dolor sit amet consectetur. Pellentesque orci imperdiet velit nisl amet iaculis quam sollicitudin. Et vehicula.',
        ],
        [
            'name' => 'T-shirt ',
            'image' => 'T-shirt-3.png',
            'alt' => 'T-shirt design 3',
            'description' => 'Lorem ipsum dolor sit amet consectetur. Pellentesque orci imperdiet velit nisl amet iaculis quam sollicitudin. Et vehicula.',
        ],
    ];
    ?>

    <main>
        <section class="t-shirt-stage" id="t-shirt-stage" aria-label="T-shirt collection">
            <h1 class="t-shrt-h1">T-shirts</h1>

            <div class="t-shirt-ring" id="t-shirt-ring">
                <?php foreach ($products as $index => $product): ?>
                    <div class="t-shirt-card" aria-hidden="true">
                        <img
                            src="/assets/t-shirt-imgs/<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($product['alt'], ENT_QUOTES, 'UTF-8') ?>"
                            draggable="false">
                    </div>
                <?php endforeach; ?>

                <section class="t-shirt-info" id="t-shirt-info" aria-live="polite">
                    <h2 class="product-tittle" id="t-shirt-name"><?= htmlspecialchars($products[0]['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p class="product-info" id="t-shirt-description"><?= htmlspecialchars($products[0]['description'], ENT_QUOTES, 'UTF-8') ?></p>
                    <div class="t-shirt-controls">
                        <label class="t-shirt-size" for="t-shirt-size">
                            <span>Size</span>
                            <select id="t-shirt-size" name="size">
                                <option>S</option>
                                <option>M</option>
                                <option>L</option>
                                <option>XL</option>
                                <option selected>XXL</option>
                            </select>
                        </label>
                        <button class="t-shirt-action" type="button" id="t-shirt-add">Add to Cart</button>
                    </div>
                    <div class="t-shirt-controls t-shirt-controls-end">
                        <button class="t-shirt-action" type="button" id="t-shirt-buy">Buy Now</button>
                    </div>
                </section>
            </div>

            <p class="t-shirt-hint">Scroll To Browse</p>
        </section>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <script>
        (function () {
            if (!window.gsap || !window.ScrollTrigger) {
                throw new Error('The T-shirt carousel requires GSAP and ScrollTrigger.');
            }

            history.scrollRestoration = 'manual';
            gsap.registerPlugin(ScrollTrigger);

            var products = <?= json_encode($products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
            var stage = document.getElementById('t-shirt-stage');
            var ring = document.getElementById('t-shirt-ring');
            var info = document.getElementById('t-shirt-info');
            var cards = Array.prototype.slice.call(ring.querySelectorAll('.t-shirt-card'));
            var name = document.getElementById('t-shirt-name');
            var description = document.getElementById('t-shirt-description');
            var addButton = document.getElementById('t-shirt-add');
            var buyButton = document.getElementById('t-shirt-buy');
            var count = products.length;
            var laps = 3;
            var direction = 1;
            var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            var cardWidth;
            var cardHeight;
            var radius;
            var step;
            var notch;
            var current = count;
            var target = count;
            var lastDrawn = null;
            var lastIndex = -1;
            var glideTween = null;

            function layout() {
                var viewportWidth = window.innerWidth;
                var viewportHeight = window.innerHeight;
                var mobile = viewportWidth < 760;

                if (mobile) {
                    cardWidth = Math.max(170, Math.min(viewportWidth * 0.58, 280, (viewportHeight - 430) / 1.26));
                } else {
                    var widthAtReferenceScale = viewportWidth * 0.175;
                    var widthAllowedByHeight = viewportHeight * (336 / 911);
                    cardWidth = Math.max(170, Math.min(widthAtReferenceScale, widthAllowedByHeight));
                }
                cardHeight = cardWidth * 1.26;
                step = mobile ? 27 : 20;
                radius = mobile ? viewportWidth * 0.95 : viewportWidth * 0.635;
                notch = Math.max(320, viewportHeight * 0.5);
                stage.style.setProperty('--t-shirt-card-width', cardWidth + 'px');
                stage.style.setProperty('--t-shirt-card-height', cardHeight + 'px');
            }

            function wrap(value) {
                return ((value + count / 2) % count + count) % count - count / 2;
            }

            function clamp(value, min, max) {
                return Math.max(min, Math.min(max, value));
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

                    var angle = offset * step * direction;
                    var radians = angle * Math.PI / 180;
                    var opacity = distance <= 1 ? 1 : clamp(1 - (distance - 1) / 0.95, 0, 1);
                    gsap.set(card, {
                        x: radius * Math.sin(radians),
                        y: radius * (1 - Math.cos(radians)),
                        rotation: angle,
                        opacity: opacity,
                        visibility: opacity > 0.01 ? 'visible' : 'hidden',
                        zIndex: Math.round(100 - distance * 10)
                    });
                });

                var nearest = Math.round(current);
                var distanceFromNearest = Math.abs(current - nearest);
                var infoOpacity = clamp(1 - (distanceFromNearest - 0.03) / 0.17, 0, 1);
                info.style.opacity = infoOpacity;
                info.style.visibility = infoOpacity < 0.02 ? 'hidden' : 'visible';
                info.style.pointerEvents = infoOpacity > 0.6 ? 'auto' : 'none';
                info.style.transform = 'translate(-50%,' + ((1 - infoOpacity) * 10).toFixed(1) + 'px)';

                var productIndex = ((nearest % count) + count) % count;
                if (productIndex !== lastIndex) {
                    lastIndex = productIndex;
                    setProduct(productIndex);
                }
            }

            layout();
            stage.classList.add('t-shirt-carousel-ready');

            var scrollTrigger = ScrollTrigger.create({
                trigger: stage,
                start: 'top top',
                end: function () {
                    return '+=' + (count * laps * notch);
                },
                pin: true,
                invalidateOnRefresh: true,
                onUpdate: function (self) {
                    var progress = (self.scroll() - self.start) / notch;
                    var lap = Math.floor(progress / count);

                    if (lap !== 1) {
                        var shift = (1 - lap) * count;
                        self.scroll(self.start + (progress + shift) * notch);
                        current += shift;
                        target += shift;
                        progress += shift;
                    }

                    target = progress;
                }
            });

            gsap.ticker.add(function (time, deltaTime) {
                var easing = reducedMotion ? 1 : 1 - Math.pow(1 - 0.12, deltaTime / 16.67);
                current += (target - current) * easing;
                if (Math.abs(target - current) < 0.0004) {
                    current = target;
                }
                if (current !== lastDrawn) {
                    lastDrawn = current;
                    render();
                }
            });

            function glide(distance, duration) {
                if (glideTween) {
                    glideTween.kill();
                }

                var progress = { value: 0 };
                var previous = 0;
                glideTween = gsap.to(progress, {
                    value: distance,
                    duration: reducedMotion ? 0.01 : duration,
                    ease: 'power2.inOut',
                    onUpdate: function () {
                        var movement = progress.value - previous;
                        previous = progress.value;
                        window.scrollBy(0, movement);
                    }
                });
            }

            ['wheel', 'touchstart'].forEach(function (eventName) {
                window.addEventListener(eventName, function () {
                    if (glideTween) {
                        glideTween.kill();
                    }
                }, { passive: true });
            });

            ScrollTrigger.addEventListener('scrollEnd', function () {
                var progress = (scrollTrigger.scroll() - scrollTrigger.start) / notch;
                var nearest = Math.round(progress);
                if (Math.abs(progress - nearest) * notch > 2) {
                    glide((nearest - progress) * notch, 0.45);
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

            function parkAt(progress) {
                window.scrollTo(0, scrollTrigger.start + progress * notch);
            }

            ScrollTrigger.refresh();
            parkAt(count);
            current = target = count;
            render();

            var resizeTimeout;
            window.addEventListener('resize', function () {
                window.clearTimeout(resizeTimeout);
                resizeTimeout = window.setTimeout(function () {
                    var currentTarget = target;
                    layout();
                    ScrollTrigger.refresh();
                    parkAt(currentTarget);
                    lastDrawn = null;
                }, 150);
            });
        })();
    </script>
</body>
</html>
