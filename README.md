# Hướng dẫn cấu hình theme WordPress

Tài liệu về cấu trúc thư mục, thứ tự chọn template, template-parts và custom field tự viết thay ACF.

## Bật GitHub Pages

1. Đưa toàn bộ thư mục này lên một repo GitHub (nhánh `main`).
2. Vào **Settings → Pages**.
3. Ở **Build and deployment**, chọn **Deploy from a branch**, nhánh `main`, thư mục `/ (root)`, rồi **Save**.
4. Sau 1-2 phút, trang có tại `https://<tên-github>.github.io/<tên-repo>/`.

## Nội dung

- `index.html`: trang hướng dẫn (một file, không cần build).
- `theme-code/`: đặt mã nguồn mẫu của theme (`core.php`, `helpers.php`, `term-meta.php`, `admin-fields.js`, `template-loader.php`) để người đọc tải về.
- `.nojekyll`: bảo GitHub Pages phục vụ file nguyên trạng.