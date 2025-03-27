function printDiv(divID, orderType = null, orderId = null) {
    var markedCheckbox = document.querySelectorAll(
        'input[type="checkbox"]:checked'
    );
    var print_item = [];
    for (var checkbox of markedCheckbox) {
        print_item.push(parseInt(checkbox.value));
    }

    $.ajax({
        type: "ajax",
        dataType: "json",
        method: "post",
        data: {
            print_item: print_item,
        },
        url: "/admin/get-order/" + $("#order_id").val() + "/" + divID,
        async: false,
        success: function (response) {
            "use strict";
            let oldPage = document.body.innerHTML;
            var css = "";

            if (Array.isArray(response.html)) {
                response.html.forEach((item) => {
                    document.body.innerHTML =
                        "<html><head><title></title><style>" +
                        css +
                        "</style></head>" +
                        item +
                        "</html>";

                    const head = document.head;
                    while (head.firstChild) {
                        head.removeChild(head.firstChild);
                    }
                    window.print();
                });
            } else {
                if (response.html) {
                    document.body.innerHTML =
                        "<html><head><title></title><style>" +
                        css +
                        "</style></head>" +
                        response.html +
                        "</html>";

                    const head = document.head;
                    while (head.firstChild) {
                        head.removeChild(head.firstChild);
                    }

                    const headerImage = document.querySelector(".header img");

                    if (headerImage.complete) {
                        window.scrollTo(0, 0);
                        window.print();
                        window.location.reload();
                    } else {
                        headerImage.onload = function () {
                            window.scrollTo(0, 0);
                            window.print();
                            window.location.reload();
                        };
                    }
                }
            }
            if (divID == "invoice-kitchen-print") {
                window.location.reload();
            }
            // document.body.innerHTML = oldPage;
            // window.location.reload();
        },
    });
}
