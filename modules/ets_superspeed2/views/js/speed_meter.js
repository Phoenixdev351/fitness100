/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
function updateSpeedMeter(value) {
    var opts = {};
    var tmp_opts = opts;
    tmp_opts.renderTicks = {};
    demoGauge.set(parseFloat(value));
    if(value<=1)
        var ext_class='color1';
    if(value>1 && value <=5)
        var ext_class='color2';
    if(value>5 && value <=10)
        var ext_class='color3';
    if(value>10 && value <=15)
        var ext_class='color4';
    if(value>15 && value <=20)
        var ext_class='color5';
    if(value>20 && value <=25)
        var ext_class='color6';
    if(value >25)
        var ext_class='color7';
    document.getElementById("speed-preview-textfield").className = "speed-preview-textfield "+ext_class; 
    demoGauge.setTextField(document.getElementById("speed-preview-textfield"));
   	AnimationUpdater.run();
  }
  function initSpeedMeterNew(value){
    demoGauge = new Gauge(document.getElementById("speed-canvas-preview"));
    var bigFont = "14px sans-serif";
    var opts = {
      angle: -0.1,
      radiusScale:1,
      lineWidth: 0.4,
      pointer: {
        length: 0.6,
        strokeWidth: 0.05,
        color: '#000000'
      },
      renderTicks: {
        divisions: 30,
        divColor: "#330E05",
        divLength: 0.12,
        subdivisions:5,
      },
      staticLabels: {
        font: "10px sans-serif",
        labels: [
        {label:0},
        {label:1}, 
        {label:5},
        {label:10}, 
        {label:15},
        {label:20}, 
        {label:25}, 
        {label:30}],
        fractionDigits: 1
      },
      staticZones: [
        {strokeStyle: "rgb(0,177,0)", min: 0, max: 1, height: 1.2},
        {strokeStyle: "rgb(165,228,54)", min: 1, max: 5, height: 1.2},
        {strokeStyle: "rgb(245,219,0)", min: 5, max: 10, height: 1.2},
        {strokeStyle: "rgb(255,177,27)", min: 10, max: 15, height: 1.2},
        {strokeStyle: "rgb(252,91,7)", min: 15, max: 20, height: 1.2},
        {strokeStyle: "rgb(251,47,0)", min: 20, max: 25, height: 1.2},
        {strokeStyle: "rgb(177,0,0)", min: 25, max: 30, height: 1.2},
      ],
      radiusScale: 0.9,
      limitMax: false,
      limitMin: false,
      highDpiSupport: false
    };
    demoGauge.setOptions(opts);
    if(value<=1)
        var ext_class='color1';
    if(value>1 && value <=5)
        var ext_class='color2';
    if(value>5 && value <=10)
        var ext_class='color3';
    if(value>10 && value <=15)
        var ext_class='color4';
    if(value>15 && value <=20)
        var ext_class='color5';
    if(value>20 && value <=25)
        var ext_class='color6';
    if(value >25)
        var ext_class='color7';
    document.getElementById("speed-preview-textfield").className = "speed-preview-textfield "+ext_class; 
    demoGauge.setTextField(document.getElementById("speed-preview-textfield"));
    demoGauge.minValue = 0;
    demoGauge.maxValue = 30;
    demoGauge.set(value);
  };
  
  $(function() {
    var params = {};
    $('input[name="currval"]').change(function(){
       updateSpeedMeter(); 
    });
  });