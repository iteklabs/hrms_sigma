<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.salary_deduction`)" class="p-0" />
        </template>

        <template #breadcrumb>
            <a-breadcrumb separator="-" style="font-size: 12px">
                <a-breadcrumb-item>
                    <router-link :to="{ name: 'admin.dashboard.index' }">
                        {{ $t(`menu.dashboard`) }}
                    </router-link>
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.payrolls`) }}
                </a-breadcrumb-item>
                <a-breadcrumb-item>
                    {{ $t(`menu.salary_deduction`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-filters>
        <a-row :gutter="[16, 16]">
            <a-col :xs="24" :sm="24" :md="12" :lg="6" :xl="6">
                <a-space>
                    <template 
                        v-if="
                            permsArray.includes('salary_adjustment_add_edit') ||
                            permsArray.includes('admin')
                        "
                    >
                        <a-space>
                            <a-button type="primary"  @click="addItemData('GovLoan')">
                                <PlusOutlined />
                                Add Government Loan
                            </a-button>
                            
                        </a-space>
                    </template>


                    <template 
                        v-if="
                            permsArray.includes('salary_adjustment_add_edit') ||
                            permsArray.includes('admin')
                        "
                    >
                        <a-space>
                            <a-button type="primary"  @click="addItemData('CompLoan')">
                                <PlusOutlined />
                                Add Company Loan
                            </a-button>
                            
                        </a-space>
                    </template>
                    
                </a-space>
            </a-col>
            
        </a-row>
    </admin-page-filters>

    <admin-page-table-content>
        <AddEditSSS
            :addEditType="addEditType"
            :visible="addEditVisible"
            :url="addEditUrl"
            @addEditSuccess="addEditSuccess"
            @closed="onCloseAddEdit"
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
            @addListSuccess="reSetFormData"
            :DataNeed="dataNeed"
        />
        <a-row>
            <a-col :span="24">
                <!-- Top-level tabs -->
                <a-tabs v-model:activeKey="topTab" @change="setUrlData">
                    <!-- Government Loan tab -->
                    <a-tab-pane key="GL" :tab="`Government Loan`">
                        <!-- Nested tabs inside Government Loan -->
                        <a-tabs v-model:activeKey="govLoanTab">
                            <a-tab-pane key="SSSsalary" tab="SSS Salary Loan">
                                <a-table :columns="sssSalaryColumns" :data-source="sssData" row-key="id" />
                            </a-tab-pane>
                            <a-tab-pane key="SSScalamity" tab="SSS Calamity Loan">
                                <a-table :columns="sssCalamityColumns" :data-source="sssData" row-key="id" />
                            </a-tab-pane>
                            <a-tab-pane key="PAGIBIGsalary" tab="Pag-IBIG Salary Loan">
                                <a-table :columns="pagibigSalaryColumns" :data-source="pagibigData" row-key="id" />
                            </a-tab-pane>
                            <a-tab-pane key="PAGIBIGcalamity" tab="Pag-IBIG Calamity Loan">
                                <a-table :columns="pagibigCalamityColumns" :data-source="pagibigData" row-key="id" />
                            </a-tab-pane>
                        </a-tabs>
                    </a-tab-pane>

                    <!-- Company Loan tab -->
                    <a-tab-pane key="CL" tab="Company Loan">
                        <a-table :columns="companyLoanColumns" :data-source="companyLoanData" row-key="id" />
                    </a-tab-pane>
                </a-tabs>
            </a-col>
        </a-row>
    </admin-page-table-content>
    <user-view-page :visible="userOpen" :userId="userId" @closed="closeUser" />

</template>

<script>
import { PlusOutlined } from "@ant-design/icons-vue";
import { ref } from "vue";
import UserInfo from "../../../../common/components/user/UserInfo.vue";
import common from "../../../../common/composable/common";
import AdminPageHeader from "../../../../common/layouts/AdminPageHeader.vue";
import AddEditSSS from "./AddEditSSS.vue";

import crud from "../../../../common/composable/crud";
export default {
    components: {
        AdminPageHeader,
        UserInfo,
        AddEditSSS,
        PlusOutlined,
        

    },
    setup() {
        const {
            permsArray,
            formatAmountCurrency,
            dayjs
        } = common();

        
        const topTab = ref("GL"); // top-level tab
        const govLoanTab = ref("SSS"); // nested tab
        const userOpen = ref(false);
        const userId = ref(undefined);
        const crudVariables = crud();
        const dataNeed = ref('');

        // Example columns
        const sssSalaryColumns = [
            { title: "User", dataIndex: "user_id" },
            { title: "Voucher/SOA", dataIndex: "loan_id" },
            { title: "Type of Loan", dataIndex: "type_loan" },
            { title: "Amount", dataIndex: "amount" },
            { title: "Action", dataIndex: "action" },
        ];
        const sssCalamityColumns = [
            { title: "User", dataIndex: "user_id" },
            { title: "Voucher/SOA", dataIndex: "loan_id" },
            { title: "Type of Loan", dataIndex: "type_loan" },
            { title: "Amount", dataIndex: "amount" },
            { title: "Action", dataIndex: "action" },
        ];
        const pagibigSalaryColumns = [
            { title: "User", dataIndex: "user_id" },
            { title: "Voucher/SOA", dataIndex: "loan_id" },
            { title: "Type of Loan", dataIndex: "type_loan" },
            { title: "Amount", dataIndex: "amount" },
            { title: "Action", dataIndex: "action" },
        ];

        const pagibigCalamityColumns = [
            { title: "User", dataIndex: "user_id" },
            { title: "Voucher/SOA", dataIndex: "loan_id" },
            { title: "Type of Loan", dataIndex: "type_loan" },
            { title: "Amount", dataIndex: "amount" },
            { title: "Action", dataIndex: "action" },
        ];
        const companyLoanColumns = [
            { title: "User", dataIndex: "user_id" },
            { title: "Voucher/SOA", dataIndex: "loan_id" },
            { title: "Type of Loan", dataIndex: "type_loan" },
            { title: "Amount", dataIndex: "amount" },
            { title: "Action", dataIndex: "action" },
        ];

        // Example data
        const sssData = [{ id: 1, loan_id: "SSS-001", amount: 5000 }];
        const pagibigData = [{ id: 1, loan_id: "PAG-001", amount: 8000 }];
        const companyLoanData = [{ id: 1, loan_id: "CL-001", amount: 10000 }];


            const addItemData = (type) => {
                dataNeed.value = type; // 'GovLoan' or 'CompLoan'
                // console.log(crudVariables.addItem())
                crudVariables.addItem()
                // crud.addItem();
            };
        const setUrlData = () => {
            // Sync tab state to URL if needed
        };

        const closeUser = () => {
            userId.value = undefined;
            userOpen.value = false;
        };

        return {
            topTab,
            govLoanTab,
            sssCalamityColumns,
            pagibigSalaryColumns,
            companyLoanColumns,
            pagibigCalamityColumns,
            sssData,
            pagibigData,
            companyLoanData,
            sssSalaryColumns,
            setUrlData,
            permsArray,
            closeUser,
            userOpen,
            userId,
            AddEditSSS,
            dataNeed,
            addItemData,
            ...crudVariables,
        };
    },
};
</script>
