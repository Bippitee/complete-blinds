<?php

//address book single template

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="container">
    <div class="row">
        <div class="col-12">
            <h1>Address Book</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <a href="/address-book/add-address">Add Address</a>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <table class="table">
                <thead>
                    <tr>
                        <th>Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $user_id = get_current_user_id();
                    $addresses = get_user_meta($user_id, 'addresses', true);
                    if ($addresses) {
                        foreach ($addresses as $address) {
                            ?>
                            <tr>
                                <td>
                                    <?php echo $address['address']; ?>
                                </td>
                                <td>
                                    <a href="/address-book/edit-address?id=<?php echo $address['id']; ?>">Edit</a>
                                    <a href="/address-book/delete-address?id=<?php echo $address['id']; ?>">Delete</a>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php

?>
