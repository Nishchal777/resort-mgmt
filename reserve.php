<style>
    #uni_modal .modal-footer {
        display: none;
    }
</style>
<div class="container-fluid">
    <form action="" id="reserve-form">
        <input type="hidden" name="id">
        <input type="hidden" name="room_id" value="<?= isset($_GET['rid']) ? $_GET['rid'] : '' ?>">
        <fieldset>
            <legend class="text-muted">Reservation Date</legend>
            <div class="row">
                <div class="col-md-6">
                    <small class="mx-2">Check-in Date</small>
                    <input type="date" name="check_in" min="<?= date('Y-m-d', strtotime(date('Y-m-d') . " +1 day")) ?>" class="form-control form-control-sm form-control-border" required>
                </div>
                <div class="col-md-6">
                    <small class="mx-2">Check-out Date</small>
                    <input type="date" name="check_out" class="form-control form-control-sm form-control-border" min="<?= date('Y-m-d', strtotime(date('Y-m-d') . " +2 days")) ?>" required>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend class="text-muted">Reservor Details</legend>
            <div class="row">
                <div class="col-md-8">
                    <small class="mx-2">Fullname</small>
                    <input type="text" name="fullname" class="form-control form-control-sm form-control-border" placeholder="username" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <small class="mx-2">Contact #</small>
                    <input type="text" name="contact" id="contact" class="form-control form-control-sm form-control-border" placeholder="9#########" required>
                </div>
                <div class="col-md-6">
                    <small class="mx-2">Email</small>
                    <input type="email" name="email" id="email"class="form-control form-control-sm form-control-border" placeholder="email" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <small class="mx-2">Address</small>
                    <textarea rows="3" name="address" class="form-control form-control-sm" placeholder="" required></textarea>
                </div>
            </div>
        </fieldset>
        <hr>
<center><p style="color:red">(Note: Once your reservation is confirmed, it cannot be cancelled.
        In case of emergency contact to Resort.)
        </p><center>
        <div class="my-2 text-right">
            <button class="btn btn-primary btn-flat btn-sm">Submit Reservation</button>
            <button class="btn btn-dark btn-flat btn-sm" type="button" data-dismiss='modal'><i class="fa fa-times"></i> Close</button>
        </div>
    </form>
</div>

<script>
    $(function () {
        $('#reserve-form').submit(function (e) {
            e.preventDefault();
            var _this = $(this)
            $('.pop-msg').remove()
            var el = $('<div>')
            el.addClass("pop-msg alert")
            el.hide()

            // Validate phone number
            var contact = $('#contact').val();
            var phoneRegex = /^(97|98)\d{8}$/;
            if (!phoneRegex.test(contact)) {
                el.addClass("alert-danger")
                el.text("The contact number must have 10 digits and start with 97 or 98.")
                _this.prepend(el)
                el.show('slow')
                $('html,body').animate({ scrollTop: 0 }, 'fast')
                return false;
            }
            var email = $('#email').val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                el.addClass("alert-danger");
                el.text("Please enter a valid email address.");
                _this.prepend(el);
                el.show('slow');
                $('html,body').animate({ scrollTop: 0 }, 'fast');
                return false;
            }

            start_loader();
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=save_reservation",
                data: new FormData($(this)[0]),
                cache: false,
                contentType: false,
                processData: false,
                method: 'POST',
                type: 'POST',
                dataType: 'json',
                error: err => {
                    console.log(err)
                    alert_toast("An error occurred", 'error');
                    end_loader();
                },
                success: function (resp) {
                    if (resp.status == 'success') {
                        // alert_toast("Success",'success')
                        location.reload();
                    } else if (!!resp.msg) {
                        el.addClass("alert-danger")
                        el.text(resp.msg)
                        _this.prepend(el)
                    } else {
                        el.addClass("alert-danger")
                        el.text("An error occurred due to unknown reason.")
                        _this.prepend(el)
                    }
                    el.show('slow')
                    $('html,body').animate({ scrollTop: 0 }, 'fast')
                    end_loader();
                }
            })
        })
    })
</script>
