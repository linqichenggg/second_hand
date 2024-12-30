// 添加到购物车的函数
function addToCart(itemId) {
    // 创建一个表单并提交
    var form = document.createElement('form');
    form.method = 'POST';
    form.action = 'item_details.php?item_id=' + itemId;

    // 添加到购物车的标记
    var input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'add_to_cart';
    input.value = '1';
    form.appendChild(input);

    // 添加商品ID
    var itemInput = document.createElement('input');
    itemInput.type = 'hidden';
    itemInput.name = 'item_id';
    itemInput.value = itemId;
    form.appendChild(itemInput);

    // 添加表单到页面并提交
    document.body.appendChild(form);
    form.submit();
}

// 删除购物车商品的函数
function deleteCartItem(cartItemId) {
    if (!confirm('确定要删除这个商品吗？')) {
        return;
    }
    
    // 获取表单并提交
    var deleteForm = document.getElementById('delete_form_' + cartItemId);
    if (deleteForm) {
        // 使用 FormData 获取表单数据
        var formData = new FormData(deleteForm);
        
        // 使用 fetch 发送请求
        fetch('cart.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // 删除成功后直接移除商品卡片
            const cartItem = document.querySelector(`[data-cart-item="${cartItemId}"]`);
            if (cartItem) {
                cartItem.remove();
                
                // 检查是否还有商品
                const remainingItems = document.querySelectorAll('[data-cart-item]');
                if (remainingItems.length === 0) {
                    // 显示空购物车提示
                    const container = document.querySelector('.container');
                    container.innerHTML = `
                        <div class="card" style="text-align: center; padding: 2rem;">
                            <p style="color: var(--text-secondary); margin-bottom: 1rem;">您的购物车是空的</p>
                            <a href="index.php" class="btn btn-primary">去逛逛</a>
                        </div>
                    `;
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('删除失败，请重试');
        });
    }
}

// 图片上传预览
function previewImage(input) {
    var preview = document.getElementById('image-preview');
    var file = input.files[0];
    if (file) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }
} 