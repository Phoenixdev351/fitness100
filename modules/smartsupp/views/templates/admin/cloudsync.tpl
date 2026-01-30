<div id="prestashop-cloudsync"></div>

{if isset($smartsupp.url.cloudSyncPathCDC)}
    <script src="{$smartsupp.url.cloudSyncPathCDC|escape:'htmlall':'UTF-8'}"></script>
{/if}

<script>
    const cdc = window.cloudSyncSharingConsent;
    cdc.init('#prestashop-cloudsync');
    cdc.on('OnboardingCompleted', (isCompleted) => {
        console.log('OnboardingCompleted', isCompleted);
    });
    cdc.isOnboardingCompleted((isCompleted) => {
        console.log('Onboarding is already Completed', isCompleted);
    });
</script>