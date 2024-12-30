document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('registerForm');
    if (form) {
        form.onsubmit = function(e) {
            e.preventDefault();
            var username = form.querySelector('[name="username"]').value;
            var password = form.querySelector('[name="password"]').value;
            var studentId = form.querySelector('[name="student_id"]').value;
            var phoneNumber = form.querySelector('[name="phone_number"]').value;
            var campus = form.querySelector('[name="campus"]').value;

            // 验证用户名
            if (username.length < 2 || username.length > 50) {
                alert('用户名长度必须在2-50个字符之间');
                return false;
            }

            // 验证密码
            if (password.length < 6 || password.length > 20) {
                alert('密码长度必须在6-20个字符之间');
                return false;
            }

            // 验证学号
            if (!/^\d{8,12}$/.test(studentId)) {
                alert('请输入正确的学号（8-12位数字）');
                return false;
            }

            // 验证手机号（如果填写了的话）
            if (phoneNumber && !/^1[3-9]\d{9}$/.test(phoneNumber)) {
                alert('请输入正确的手机号码');
                return false;
            }

            // 验证校区
            if (!campus) {
                alert('请选择校区');
                return false;
            }

            // 如果验证通过，直接提交表单
            form.submit();
            return true;
        };
    }
}); 