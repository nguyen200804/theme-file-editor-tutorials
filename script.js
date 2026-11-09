// script.js - Tự động thêm nút Copy cho tất cả các khối code <pre>
document.addEventListener('DOMContentLoaded', () => {
    // Hàm copy an toàn hỗ trợ cả clipboard API và fallback execCommand
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        } else {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                document.body.removeChild(textarea);
                return Promise.resolve();
            } catch (err) {
                document.body.removeChild(textarea);
                return Promise.reject(err);
            }
        }
    }

    document.querySelectorAll('pre').forEach((pre) => {
        // Tạo nút Copy
        const btn = document.createElement('button');
        btn.className = 'copy-btn';
        btn.type = 'button';
        btn.innerText = 'Copy';
        btn.setAttribute('aria-label', 'Copy đoạn code');

        btn.addEventListener('click', () => {
            const code = pre.querySelector('code');
            const textToCopy = (code ? code.innerText : pre.innerText).trim();

            copyToClipboard(textToCopy).then(() => {
                btn.innerText = '✓ Đã copy';
                btn.classList.add('copied');
                setTimeout(() => {
                    btn.innerText = 'Copy';
                    btn.classList.remove('copied');
                }, 2000);
            }).catch(() => {
                btn.innerText = 'Lỗi copy';
                setTimeout(() => {
                    btn.innerText = 'Copy';
                }, 2000);
            });
        });

        pre.appendChild(btn);
    });
});

