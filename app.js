const cursor = document.querySelector(".click-svg");
const hoverAreas = document.querySelectorAll(".home-pg-btn");

if (cursor && hoverAreas.length && window.gsap) {
    gsap.set(cursor, {
        xPercent: -50,
        yPercent: -50,
        scale: 0,
    });

    const moveX = gsap.quickTo(cursor, "x", {
        duration: 0.2,
        ease: "power3",
    });
    const moveY = gsap.quickTo(cursor, "y", {
        duration: 0.2,
        ease: "power3",
    });

    hoverAreas.forEach((area) => {
        area.addEventListener("pointerenter", (event) => {
            gsap.set(cursor, {
                x: event.clientX,
                y: event.clientY,
            });
            gsap.to(cursor, {
                scale: 1,
                duration: 0.25,
                ease: "power2.out",
            });
        });

        area.addEventListener("pointermove", (event) => {
            moveX(event.clientX);
            moveY(event.clientY);
        });

        area.addEventListener("pointerleave", (event) => {
            const nextTarget = event.relatedTarget;
            if (nextTarget instanceof Element && nextTarget.closest(".home-pg-btn")) {
                return;
            }

            gsap.to(cursor, {
                scale: 0,
                duration: 0.2,
                ease: "power2.in",
            });
        });
    });
}