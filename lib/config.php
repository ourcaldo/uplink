<?php

declare(strict_types=1);

const GATE_ONE_URL = 'https://alco.camarjaya.co.id';
const GATE_TWO_URL = 'https://elco.camarjaya.co.id';

const ARTICLE_DIRECTORY = __DIR__ . '/../content/legal-articles';

$adsenseAds = [
    <<<'HTML'
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7705241191817847" crossorigin="anonymous"></script>
<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7705241191817847" data-ad-slot="7940307370" data-ad-format="auto" data-full-width-responsive="true"></ins>
<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
HTML,
    <<<'HTML'
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7705241191817847" crossorigin="anonymous"></script>
<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7705241191817847" data-ad-slot="7232242279" data-ad-format="auto" data-full-width-responsive="true"></ins>
<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
HTML,
    <<<'HTML'
<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-7705241191817847" crossorigin="anonymous"></script>
<ins class="adsbygoogle" style="display:block" data-ad-client="ca-pub-7705241191817847" data-ad-slot="5598235591" data-ad-format="auto" data-full-width-responsive="true"></ins>
<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
HTML,
];

$alcoOnlyAds = [
    '<script src="https://pl28941074.profitablecpmratenetwork.com/d3/04/70/d30470a93c08ea311718c1efec694c7a.js"></script>',
    '<a class="smartlink-btn" href="https://www.profitablecpmratenetwork.com/jae56x7b?key=ae24e334cd0c65b4bf80b5fb2ca57314" rel="nofollow sponsored noopener" target="_blank">Recommended Offer</a>',
    '<script async="async" data-cfasync="false" src="https://pl28941089.profitablecpmratenetwork.com/34a7207d08fa8599d6e064a9936290b6/invoke.js"></script><div id="container-34a7207d08fa8599d6e064a9936290b6"></div>',
    '<script src="https://pl28941094.profitablecpmratenetwork.com/ae/8e/c8/ae8ec8de66c19aabd5a595a3c3846a96.js"></script>',
    '<script>atOptions = {"key":"be95148531d47af74f91b8e66eb616a2","format":"iframe","height":60,"width":468,"params":{}};</script><script src="https://www.highperformanceformat.com/be95148531d47af74f91b8e66eb616a2/invoke.js"></script>',
    '<script>atOptions = {"key":"a89c070edb0dbbd5d3383580f5d0ca48","format":"iframe","height":300,"width":160,"params":{}};</script><script src="https://www.highperformanceformat.com/a89c070edb0dbbd5d3383580f5d0ca48/invoke.js"></script>',
    '<script>atOptions = {"key":"579d8510e28e6cf4ed7ed5941d073e0f","format":"iframe","height":50,"width":320,"params":{}};</script><script src="https://www.highperformanceformat.com/579d8510e28e6cf4ed7ed5941d073e0f/invoke.js"></script>',
    '<script>atOptions = {"key":"eaba15a9f6cfdef37905ca185d9f6056","format":"iframe","height":250,"width":300,"params":{}};</script><script src="https://www.highperformanceformat.com/eaba15a9f6cfdef37905ca185d9f6056/invoke.js"></script>',
    '<script>atOptions = {"key":"b8ab0b7bcb8fba6833ec41d3c7d5f74f","format":"iframe","height":600,"width":160,"params":{}};</script><script src="https://www.highperformanceformat.com/b8ab0b7bcb8fba6833ec41d3c7d5f74f/invoke.js"></script>',
    '<script>atOptions = {"key":"c50e2d43c3b531b3fa3ed8d91e602079","format":"iframe","height":90,"width":728,"params":{}};</script><script src="https://www.highperformanceformat.com/c50e2d43c3b531b3fa3ed8d91e602079/invoke.js"></script>',
];

$elcoOnlyAds = [
    '<script src="https://pl28941079.profitablecpmratenetwork.com/5a/9a/a5/5a9aa5a59ef1a526a7910ed3fa9a0764.js"></script>',
    '<a class="smartlink-btn" href="https://www.profitablecpmratenetwork.com/g7mec483qx?key=f2d17ac4dd1c036ed1597dc74f2b50c2" rel="nofollow sponsored noopener" target="_blank">Special Offer</a>',
    '<script async="async" data-cfasync="false" src="https://pl28941090.profitablecpmratenetwork.com/bd4461afdd9b5595f3e1c0295bb6e2ba/invoke.js"></script><div id="container-bd4461afdd9b5595f3e1c0295bb6e2ba"></div>',
    '<script src="https://pl28941097.profitablecpmratenetwork.com/e1/21/18/e12118e428907b3b9777f82dee8229db.js"></script>',
    '<script>atOptions = {"key":"2886891b43e7c55d0b03bc90a5a62c2c","format":"iframe","height":60,"width":468,"params":{}};</script><script src="https://www.highperformanceformat.com/2886891b43e7c55d0b03bc90a5a62c2c/invoke.js"></script>',
    '<script>atOptions = {"key":"9f969a7a5752af23a4a02b926dddf69c","format":"iframe","height":300,"width":160,"params":{}};</script><script src="https://www.highperformanceformat.com/9f969a7a5752af23a4a02b926dddf69c/invoke.js"></script>',
    '<script>atOptions = {"key":"1d79ae3ea577fa85feba0583bb38cb8c","format":"iframe","height":50,"width":320,"params":{}};</script><script src="https://www.highperformanceformat.com/1d79ae3ea577fa85feba0583bb38cb8c/invoke.js"></script>',
    '<script>atOptions = {"key":"d28cb6d9f0d282664275b78b13159be0","format":"iframe","height":250,"width":300,"params":{}};</script><script src="https://www.highperformanceformat.com/d28cb6d9f0d282664275b78b13159be0/invoke.js"></script>',
    '<script>atOptions = {"key":"8eb357a2fa6c43aebb1d1c2cabe433cf","format":"iframe","height":600,"width":160,"params":{}};</script><script src="https://www.highperformanceformat.com/8eb357a2fa6c43aebb1d1c2cabe433cf/invoke.js"></script>',
    '<script>atOptions = {"key":"a27d54f838c59355e002f5e79d3b8992","format":"iframe","height":90,"width":728,"params":{}};</script><script src="https://www.highperformanceformat.com/a27d54f838c59355e002f5e79d3b8992/invoke.js"></script>',
];

$adsByGate = [
    'alco' => array_merge($alcoOnlyAds, $adsenseAds),
    'elco' => array_merge($elcoOnlyAds, $adsenseAds),
];
