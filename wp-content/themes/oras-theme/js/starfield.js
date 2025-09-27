(function(){
  const nebulaCanvas = document.getElementById('nebula-canvas');
  const nebCtx = nebulaCanvas.getContext('2d');

  const starCanvas = document.getElementById('star-canvas');
  const starCtx = starCanvas.getContext('2d');

  let layers = [];
  const TOTAL_LAYERS = 4;
  const TOTAL_STARS = 6000;
  const shootingStars = [];
  let nebulaBlobs = [];

function resizeCanvas() {
    const dpr = window.devicePixelRatio || 1;
    [nebulaCanvas, starCanvas].forEach(c => {
        c.width = Math.floor(window.innerWidth * dpr);
        c.height = Math.floor(window.innerHeight * dpr);
        c.style.width = window.innerWidth + 'px';
        c.style.height = window.innerHeight + 'px';
        const ctx = c.getContext('2d');
        ctx.setTransform(1,0,0,1,0,0);
        ctx.scale(dpr,dpr);
    });
}

// Ensure mobile layout triggers resize
window.addEventListener('load', resizeCanvas);
window.addEventListener('orientationchange', resizeCanvas);


  function resizeCanvas(){
    const dpr = window.devicePixelRatio || 1;
    [nebulaCanvas, starCanvas].forEach(c => {
      c.width = Math.floor(window.innerWidth * dpr);
      c.height = Math.floor(window.innerHeight * dpr);
      c.style.width = window.innerWidth + 'px';
      c.style.height = window.innerHeight + 'px';
      const ctx = c.getContext('2d');
      ctx.setTransform(1,0,0,1,0,0);
      ctx.scale(dpr, dpr);
    });
  }

  function hexToRgb(hex){
    hex = hex.replace('#','');
    const bigint = parseInt(hex, 16);
    const r = (bigint >> 16) & 255;
    const g = (bigint >> 8) & 255;
    const b = bigint & 255;
    return `${r},${g},${b}`;
  }

  // ----------------- Nebula Generation -----------------
  function createNebula(){
    nebulaBlobs = [];
    const w = window.innerWidth;
    const h = window.innerHeight;

    const nebulaColors = [
      'rgba(255,200,200,0.05)',
      'rgba(180,150,255,0.05)',
      'rgba(120,180,255,0.04)',
      'rgba(255,150,100,0.04)',
    ];

    for(let i=0;i<10;i++){
      nebulaBlobs.push({
        x: Math.random()*w,
        y: Math.random()*h,
        radius: 200 + Math.random()*300,
        color: nebulaColors[Math.floor(Math.random()*nebulaColors.length)],
        dx: (Math.random()-0.5)*0.05,
        dy: (Math.random()-0.5)*0.05
      });
    }
  }

  function drawNebula(){
    nebCtx.clearRect(0,0,window.innerWidth, window.innerHeight);
    for(const blob of nebulaBlobs){
      const gradient = nebCtx.createRadialGradient(blob.x, blob.y, 0, blob.x, blob.y, blob.radius);
      gradient.addColorStop(0, blob.color);
      gradient.addColorStop(1, 'rgba(0,0,0,0)');
      nebCtx.fillStyle = gradient;
      nebCtx.beginPath();
      nebCtx.arc(blob.x, blob.y, blob.radius, 0, Math.PI*2);
      nebCtx.fill();

      blob.x += blob.dx;
      blob.y += blob.dy;
      if(blob.x < -blob.radius) blob.x = window.innerWidth + blob.radius;
      if(blob.x > window.innerWidth + blob.radius) blob.x = -blob.radius;
      if(blob.y < -blob.radius) blob.y = window.innerHeight + blob.radius;
      if(blob.y > window.innerHeight + blob.radius) blob.y = -blob.radius;
    }
  }

  // ----------------- Starfield -----------------
  function createStars(){
    layers = [];
    const w = window.innerWidth;
    const h = window.innerHeight;

    for(let l=0;l<TOTAL_LAYERS;l++){
      let layerStars = [];
      const depthFactor = 1 + l;
      const layerCount = TOTAL_STARS / TOTAL_LAYERS;

      for(let i=0;i<layerCount;i++){
        let clusterOffsetX = 0;
        let clusterOffsetY = 0;
        if(Math.random() < 0.02) { 
          clusterOffsetX = (Math.random()-0.5)*50;
          clusterOffsetY = (Math.random()-0.5)*50;
        }

        // Base color per layer
        let baseColor = '#ffffff';
        if(l===0) baseColor = '#ffffff';
        if(l===1) baseColor = '#ffeedd';
        if(l===2) baseColor = '#fff8e0';
        if(l===3) baseColor = '#e0f0ff';

        // Tiny chance for red star
        if(Math.random() < 0.01) baseColor = '#ff6666';

        const radius = (Math.random() ** 2.5) * 1.3 + 0.1;
        const twinkleSpeed = Math.random()*0.6 + 0.1 - l*0.05;
        const baseOpacity = Math.random()*0.35 + 0.15;
        const x = Math.random()*w + clusterOffsetX;
        const y = Math.random()*h + clusterOffsetY;

        layerStars.push({
          x, y, radius, color: baseColor,
          baseOpacity, twinkleSpeed,
          phase: Math.random()*Math.PI*2,
          depth: depthFactor,
          burstPhase: -Math.random()*5,
          specialBurst: false,
          burstDuration:0,
          nextTwinkle: Math.random()*5+1
        });
      }
      layers.push(layerStars);
    }
  }

  function createShootingStar(){
    const w = window.innerWidth;
    const h = window.innerHeight;
    shootingStars.push({
      x: Math.random()*w,
      y: Math.random()*h/2,
      length: 80 + Math.random()*40,
      speed: 800 + Math.random()*400,
      angle: Math.random()*Math.PI/6 + Math.PI/12,
      life: 0
    });
  }

  function drawStars(scrollOffset, t){
    starCtx.clearRect(0,0,window.innerWidth, window.innerHeight);
    const h = window.innerHeight;

    for(const layer of layers){
      for(const s of layer){
        let y = (s.y + scrollOffset / (s.depth*10)) % h;
        if(y<0) y+=h;

        let opacity = s.baseOpacity + Math.sin(t*s.twinkleSpeed + s.phase)*0.06;

        if(!s.specialBurst && t > s.nextTwinkle){
          s.specialBurst = true;
          s.burstPhase = t;
          s.burstDuration = 0.5 + Math.random()*0.5;
          s.nextTwinkle = t + 3 + Math.random()*5;
        }

        if(s.specialBurst){
          const dt = t - s.burstPhase;
          if(dt < s.burstDuration){
            opacity += Math.sin((dt/s.burstDuration)*Math.PI)*0.15;
          } else s.specialBurst = false;
        }

        const shimmerTime = t - s.burstPhase;
        const shimmer = Math.sin(shimmerTime*3)*Math.exp(-shimmerTime*1.2);
        opacity += shimmer*0.05;

        const o = Math.max(0.1, Math.min(opacity, 0.55));

        starCtx.beginPath();
        starCtx.arc(s.x, y, s.radius, 0, Math.PI*2);
        starCtx.fillStyle = `rgba(${hexToRgb(s.color)},${o})`;
        starCtx.fill();
      }
    }

    // shooting stars
    for(let i=shootingStars.length-1;i>=0;i--){
      const star = shootingStars[i];
      star.life += 16/1000;
      const dx = Math.cos(star.angle) * star.speed * 0.016;
      const dy = Math.sin(star.angle) * star.speed * 0.016;
      star.x += dx;
      star.y += dy;

      starCtx.beginPath();
      starCtx.moveTo(star.x, star.y);
      starCtx.lineTo(star.x - dx*3, star.y - dy*3);
      starCtx.strokeStyle = `rgba(255,255,255,0.8)`;
      starCtx.lineWidth = 1.5;
      starCtx.stroke();

      if(star.x > window.innerWidth || star.y > window.innerHeight) shootingStars.splice(i,1);
    }
  }

  function animate(time){
    const scrollOffset = window.scrollY || window.pageYOffset || 0;
    drawNebula();
    drawStars(scrollOffset, time*0.001);

    if(Math.random() < 0.002) createShootingStar();
    requestAnimationFrame(animate);
  }

  function init(){
    resizeCanvas();
    createNebula();
    createStars();
    requestAnimationFrame(animate);
  }

  window.addEventListener('resize',()=>{
    clearTimeout(window._resizeTimeout);
    window._resizeTimeout = setTimeout(()=>{
      resizeCanvas();
      createNebula();
      createStars();
    },150);
  });

  init();
})();
