$(document).ready(function() {
  function addItem(listId, itemName) {
      $(`#${listId}`).append(`<li class="py-2">${itemName}</li>`);
      Swal.fire('Berhasil', `${itemName} ditambahkan!`, 'success');
  }

  $('#add-admin').click(function() {
      const adminName = prompt("Masukkan nama admin:");
      if (adminName) addItem('admins-list', adminName);
  });

  $('#add-category').click(function() {
      const categoryName = prompt("Masukkan nama kategori:");
      if (categoryName) addItem('categories-list', categoryName);
  });

  $('#add-customer').click(function() {
      const customerName = prompt("Masukkan nama customer:");
      if (customerName) addItem('customers-list', customerName);
  });

  $('#add-order').click(function() {
      const orderName = prompt("Masukkan nama order:");
      if (orderName) addItem('orders-list', orderName);
  });

  $('#add-product').click(function() {
      const productName = prompt("Masukkan nama produk:");
      if (productName) addItem('products-list', productName);
  });

  $('#add-transaction').click(function() {
      const transactionName = prompt("Masukkan nama transaksi:");
      if (transactionName) addItem('transactions-list', transactionName);
  });
});
