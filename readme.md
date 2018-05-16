- Sử dụng PHP với version >= 7

- Cấu hình lại file "php.ini" trong xampp/php

	+ upload_max_filesize = 100M
	+ post_max_size = 100M

* Cách thức hoạt động:

- Đăng ký user (mặc định là quyền "user").

- Với user có quyền là admin sẽ có tất cả chức năng ở dưới và sẽ được upload file nhạc (wav -> file này sẽ không có chữ ký khi up lên). 

- Với user có quyền khác admin:
	+ Mua nhạc (sau khi mua thì file nhạc này sẽ có chữ ký của user)
	+ Nghe nhạc (những bài hát đã mua)
	+ Kiểm tra nhạc
	
- Username + password

	+ admin - admin	
	+ tester - 123456

* Liên hệ: 0937572911 - Trương Xuân Nguyên
