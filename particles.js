class ParticleSystem {
    constructor(canvas, options = {}) {
        this.canvas = canvas;
        this.ctx = canvas.getContext('2d');
        this.particles = [];
        this.options = {
            particleCount: options.particleCount || 50,
            color: options.color || '#ffffff',
            radius: options.radius || { min: 1, max: 3 },
            speed: options.speed || { min: 0.2, max: 0.8 },
            opacity: options.opacity || { min: 0.1, max: 0.5 },
            connectParticles: options.connectParticles !== undefined ? options.connectParticles : true,
            connectionDistance: options.connectionDistance || 100
        };
        
        this.init();
        this.animate();
        
        // Handle resize
        window.addEventListener('resize', () => this.handleResize());
        this.handleResize();
    }

    init() {
        // Create particles
        for (let i = 0; i < this.options.particleCount; i++) {
            this.particles.push({
                x: Math.random() * this.canvas.width,
                y: Math.random() * this.canvas.height,
                radius: this.randomBetween(this.options.radius.min, this.options.radius.max),
                speed: {
                    x: this.randomBetween(-this.options.speed.max, this.options.speed.max),
                    y: this.randomBetween(-this.options.speed.max, this.options.speed.max)
                },
                opacity: this.randomBetween(this.options.opacity.min, this.options.opacity.max)
            });
        }
    }

    randomBetween(min, max) {
        return Math.random() * (max - min) + min;
    }

    handleResize() {
        this.canvas.width = window.innerWidth;
        this.canvas.height = window.innerHeight;
        
        // Adjust particle positions
        this.particles.forEach(particle => {
            if (particle.x > this.canvas.width) particle.x = Math.random() * this.canvas.width;
            if (particle.y > this.canvas.height) particle.y = Math.random() * this.canvas.height;
        });
    }

    drawParticle(particle) {
        this.ctx.beginPath();
        this.ctx.arc(particle.x, particle.y, particle.radius, 0, Math.PI * 2);
        this.ctx.fillStyle = `rgba(255, 255, 255, ${particle.opacity})`;
        this.ctx.fill();
    }

    drawConnections() {
        for (let i = 0; i < this.particles.length; i++) {
            for (let j = i + 1; j < this.particles.length; j++) {
                const dx = this.particles[i].x - this.particles[j].x;
                const dy = this.particles[i].y - this.particles[j].y;
                const distance = Math.sqrt(dx * dx + dy * dy);

                if (distance < this.options.connectionDistance) {
                    const opacity = (1 - distance / this.options.connectionDistance) * 0.5;
                    this.ctx.beginPath();
                    this.ctx.strokeStyle = `rgba(255, 255, 255, ${opacity})`;
                    this.ctx.lineWidth = 1;
                    this.ctx.moveTo(this.particles[i].x, this.particles[i].y);
                    this.ctx.lineTo(this.particles[j].x, this.particles[j].y);
                    this.ctx.stroke();
                }
            }
        }
    }

    updateParticles() {
        this.particles.forEach(particle => {
            particle.x += particle.speed.x;
            particle.y += particle.speed.y;

            // Bounce off edges
            if (particle.x < 0 || particle.x > this.canvas.width) particle.speed.x *= -1;
            if (particle.y < 0 || particle.y > this.canvas.height) particle.speed.y *= -1;
        });
    }

    animate() {
        this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
        
        this.updateParticles();
        
        if (this.options.connectParticles) {
            this.drawConnections();
        }
        
        this.particles.forEach(particle => this.drawParticle(particle));
        
        requestAnimationFrame(() => this.animate());
    }
}

// Initialize particles when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('particles-canvas');
    if (canvas) {
        new ParticleSystem(canvas, {
            particleCount: 75,
            radius: { min: 1, max: 3 },
            speed: { min: 0.1, max: 0.3 },
            opacity: { min: 0.1, max: 0.3 },
            connectParticles: true,
            connectionDistance: 150
        });
    }
}); 