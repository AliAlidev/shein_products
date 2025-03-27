function printDiv(divID, orderType = null, orderId = null) {

  // var markedCheckbox = document.querySelectorAll('input[type="checkbox"]:checked');
    var markedCheckbox = document.querySelectorAll('.kitchen-print:checked');
  var print_item = [];
  for (var checkbox of markedCheckbox) {
    print_item.push(parseInt(checkbox.value));
  }
  // console.log(print_item);
  // var data = e.params.data;
  $.ajax({
    'type': 'ajax',
    'dataType': 'json',
    'method': 'post',
    'data': {
      print_item:print_item,
    },
    'url': '/admin/get-order/'+ $("#order_id").val()+'/'+divID,
    'async': false,
    success: function (response) {

      'use strict';
      let oldPage     = document.body.innerHTML;
      // $("#table_div").removeClass();
      // $("#order_table_data").removeClass();

      const styleHeight = document.getElementById('invoice-print').scrollHeight;
      var css = '@page { size: 72mm '+ styleHeight+'px; }',
          head = document.head || document.getElementsByTagName('head')[0],
          style = document.createElement('style');
      //
      style.type = 'text/css';
      style.media = 'print';

      if (style.styleSheet){
        style.styleSheet.cssText = css;
      } else {
        style.appendChild(document.createTextNode(css));
      }

      head.appendChild(style);

      // const summary = document.getElementById('summary');
      // summary.classList.remove('text-right');

      // let divElements = document.getElementById(divID).innerHTML;

      document.body.innerHTML = "<html><head><title></title><style>" + css + "</style></head><body>" + response.html + "</body></html>";
      window.print();
      if (orderType == 3)
      {
        $.ajax({
          type:'GET',
          url:'/admin/dine-in-orders/send_order_email/'+orderId,
          dataType: 'json',
          'async': false,
          success:function(data) {
            console.log(orderId);
          }
        });
      }
      document.body.innerHTML = oldPage;
      window.location.reload();
    }
  });
}


