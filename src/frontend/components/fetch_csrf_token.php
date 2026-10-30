<script src="/lib/js/fetch_csrf_token.js"></script>
<script>
    const csrfTokenApiUrl = <?= json_encode(
        rtrim((string) (getenv('API_URL') ?: ''), '/'),
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
    ) ?>;

    document.addEventListener("DOMContentLoaded", async () => {
        await autoInjectCsrfToken(csrfTokenApiUrl);
    });
</script>