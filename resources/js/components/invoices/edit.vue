<script setup>
import axios from "axios";
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();

let form = ref({ id: '' })
let all_customers = ref([])
let customer_id = ref([])
let list_products = ref([])

const showModal = ref(false)
const hideModal = ref(true)

const props = defineProps({
    id: {
        type: String,
        default: ''
    }
})

onMounted(async () => {
    getInvoice()
    getAllCustomers()
    getProducts()
})

const getInvoice = async () => {
    let response = await axios.get(`/api/edit_invoice/${props.id}`)
    console.log('form', response.data.invoice)
    form.value = response.data.invoice
}

const getAllCustomers = async () => {
    let response = await axios.get('/api/customers')
    console.log('response', response.data)
    all_customers.value = response.data.customers
}

const addItem = (item) => {
    const item_cart = {
        product_id: item.id,
        item_code: item.item_code,
        description: item.description,
        unit_price: item.unit_price,
        quantity: item.quantity,
    }
    // list_cart.value.push(item_cart)
    form.value.invoice_items.push(item_cart)
    closeModal()
}

const deleteInvoiceItem = (id, index) => {
    form.value.invoice_items.splice(index, 1)

    if (id != undefined) {
        axios.get(`/api/delete_invoice_items/${id}`)
    }
}

const openModal = () => {
    showModal.value = !showModal.value
}

const closeModal = () => {
    showModal.value = !hideModal.value
}

const getProducts = async () => {
    let response = await axios.get('/api/products')
    console.log('products', response)
    list_products.value = response.data.products
}

const subTotal = () => {
    let subtotal = 0

    if (form.value.invoice_items) {
        form.value.invoice_items.map((item) => {
            subtotal += (item.quantity * item.unit_price)
        })
    }

    return subtotal
}

const grandTotal = () => {
    if (form.value.invoice_items && ((subTotal() - form.value.discount) > 0)) {
        return subTotal() - form.value.discount
    } else {
        return 0
    }
}

const onEdit = (id) => {
    if (form.value.invoice_items.length >= 1) {
        // alert(JSON.stringify(form.value.invoice_items))

        // Check if the length of list_cart.value is greater than or equal to 0
        if (form.value.invoice_items.length >= 1) {
            // Declare a variable named subtotal and assign it the value 0
            let subtotal = 0
            subtotal = subTotal()

            // Declare a variable named grandtotal and assign it the value 0
            let grandtotal = 0
            grandtotal = grandTotal()

            // Create a new instance of FormData
            const formData = new FormData()

            // Append key/value pairs to the formData object
            formData.append('invoice_item', JSON.stringify(form.value.invoice_items))
            formData.append('customer_id', form.value.customer_id)
            formData.append('date', form.value.date)
            formData.append('due_date', form.value.due_date)
            formData.append('number', form.value.number)
            formData.append('reference', form.value.reference)
            formData.append('discount', form.value.discount)
            formData.append('sub_total', subtotal)
            formData.append('total', grandtotal)
            formData.append('term', form.value.term)

            // Make a POST request to the "/api/add_invoice" endpoint, sending the formData as the request body
            axios.post(`/api/update_invoice/${form.value.id}`, formData)

            // Reset form.value to an empty array
            form.value.invoice_items = []

            // Navigate to the root route ("/")
            router.push('/')
        }
    }
}
</script>
<template>
    <div class="container">
        <!--==================== EDIT INVOICE ====================-->
        <div class="invoices">

            <div class="card__header">
                <div>
                    <h2 class="invoice__title">Edit Invoice</h2>
                </div>
                <div>

                </div>
            </div>

            <div class="card__content">
                <div class="card__content--header">
                    <div>
                        <p class="my-1">Customer</p>
                        <select name="" id="" class="input" v-model="form.customer_id">
                            <option disabled>Select Customer</option>
                            <option :value="customer.id" v-for="customer in all_customers" :key="customer.id">
                                #{{ customer.id }} {{ customer.first_name }} {{ customer.last_name }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <p class="my-1">Date</p>
                        <input id="date" placeholder="dd-mm-yyyy" type="date" class="input" v-model="form.date"> <!---->
                        <p class="my-1">Due Date</p>
                        <input id="due_date" type="date" class="input" v-model="form.due_date">
                    </div>
                    <div>
                        <p class="my-1">Number</p>
                        <input type="text" class="input" v-model="form.number" disabled>
                        <p class="my-1">Reference(Optional)</p>
                        <input type="text" class="input" v-model="form.reference">
                    </div>
                </div>
                <br><br>
                <div class="table">

                    <div class="table--heading2">
                        <p>Item Description</p>
                        <p>Unit Price</p>
                        <p>Qty</p>
                        <p>Total</p>
                        <p></p>
                    </div>

                    <!-- INVOICE ITEM(S) -->
                    <div class="table--items2" v-for="(item_cart, i) in form.invoice_items" :key="item_cart.id">
                        <p v-if="item_cart.product">
                            #{{ item_cart.product.item_code }} {{ item_cart.product.description }}
                        </p>
                        <p v-else>
                            #{{ item_cart.item_code }} {{ item_cart.description }}
                        </p>
                        <p>
                            <input type="text" class="input" v-model="item_cart.unit_price">
                        </p>
                        <p>
                            <input type="text" class="input" v-model="item_cart.quantity">
                        </p>
                        <p>
                            ৳ {{ item_cart.unit_price * item_cart.quantity }}
                        </p>
                        <p style="color: red; font-size: 24px;cursor: pointer;"
                            @click="deleteInvoiceItem(item_cart.id, i)">
                            &times;
                        </p>
                    </div>
                    <div style="padding: 10px 30px !important;">
                        <button class="btn btn-sm btn__open--modal" @click="openModal()">
                            Add New Item
                        </button>
                    </div>
                </div>

                <div class="table__footer">
                    <div class="document-footer">
                        <p>Terms and Conditions</p>
                        <textarea cols="50" rows="7" class="textarea" v-model="form.terms"></textarea>
                    </div>
                    <div>
                        <div class="table__footer--subtotal">
                            <p>Sub Total</p>
                            <span>৳ {{ subTotal() }}</span>
                        </div>
                        <div class="table__footer--discount">
                            <p>Discount</p>
                            <input type="text" class="input" v-model="form.discount">
                        </div>
                        <div class="table__footer--total">
                            <p>Grand Total</p>
                            <span>৳ {{ grandTotal() }}</span>
                        </div>
                    </div>
                </div>


            </div>
            <div class="card__header" style="margin-top: 40px;">
                <div>

                </div>
                <div>
                    <a class="btn btn-secondary" @click="onEdit(form.id)">
                        Save
                    </a>
                </div>
            </div>

        </div>
        <!--==================== add modal items ====================-->
        <div class="modal main__modal" :class="{ show: showModal }">
            <div class="modal__content">
                <span class="modal__close btn__close--modal" @click="closeModal()">×</span>
                <h3 class="modal__title">Add Item</h3>
                <hr><br>
                <div class="modal__items">
                    <ul style="list-style: none;">
                        <li v-for="(item, i) in list_products" :key="item.id"
                            style="display: grid;grid-template-columns: 30px 350px 15px;align-items: center;padding-bottom: 5px;">
                            <p>{{ i + 1 }}</p>
                            <a>{{ item.item_code }} {{ item.description }}</a>
                            <button @click="addItem(item)"
                                style="border:1px solid #e0e0e0;width: 35px;height: 35px;cursor: pointer;">
                                +
                            </button>
                        </li>
                    </ul>
                </div>
                <br>
                <hr>
                <div class="model__footer">
                    <button class="btn btn-light mr-2 btn__close--modal" @click="closeModal()">
                        Cancel
                    </button>
                    <button class="btn btn-light btn__close--modal ">Save</button>
                </div>
            </div>
        </div>

        <br><br><br>
    </div>
</template>
