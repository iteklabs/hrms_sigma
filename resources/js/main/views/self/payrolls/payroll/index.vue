<template>
    <AdminPageHeader>
        <template #header>
            <a-page-header :title="$t(`menu.payrolls`)" class="p-0" />
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
                    {{ $t(`menu.payrolls`) }}
                </a-breadcrumb-item>
            </a-breadcrumb>
        </template>
    </AdminPageHeader>

    <admin-page-filters>
        <a-row :gutter="[16, 16]">
            <a-col :xs="24" :sm="24" :md="12" :lg="10" :xl="10"> </a-col>
            <a-col :xs="24" :sm="24" :md="12" :lg="14" :xl="14">
                <a-row :gutter="[16, 16]" justify="end">
                    <a-col :xs="24" :sm="24" :md="8" :lg="8" :xl="6">
                        <a-date-picker
                            v-model:value="extraFilters.year"
                            :placeholder="
                                $t('common.select_default_text', [$t('holiday.year')])
                            "
                            picker="year"
                            @change="setUrlData"
                            style="width: 100%"
                            :allowClear="false"
                        />
                    </a-col>
                    <a-col :xs="24" :sm="24" :md="8" :lg="8" :xl="6">
                        <a-select
                            v-model:value="extraFilters.month"
                            :placeholder="
                                $t('common.select_default_text', [$t('holiday.month')])
                            "
                            :allowClear="true"
                            style="width: 100%"
                            optionFilterProp="title"
                            show-search
                            @change="setUrlData"
                        >
                            <a-select-option
                                v-for="month in monthArrays"
                                :key="month.name"
                                :value="month.value"
                            >
                                {{ month.name }}
                            </a-select-option>
                        </a-select>
                    </a-col>
                </a-row>
            </a-col>
        </a-row>
    </admin-page-filters>

    <admin-page-table-content>
        <AddEdit
            :addEditType="addEditType"
            :visible="addEditVisible"
            :url="addEditUrl"
            @addEditSuccess="addEditSuccess"
            @closed="onCloseAddEdit"
            :formData="formData"
            :data="viewData"
            :pageTitle="pageTitle"
            :successMessage="successMessage"
        />
        <a-row>
            <a-col :span="24">
                <div class="table-responsive">
                    <a-table
                        :row-selection="{
                            selectedRowKeys: table.selectedRowKeys,
                            onChange: onRowSelectChange,
                            getCheckboxProps: (record) => ({
                                disabled: false,
                                name: record.xid,
                            }),
                        }"
                        :columns="columns"
                        :row-key="(record) => record.xid"
                        :data-source="table.data"
                        :pagination="table.pagination"
                        :loading="table.loading"
                        @change="handleTableChange"
                        bordered
                        size="middle"
                    >
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.dataIndex == 'payment_date'">
                                {{
                                    record.payment_date != null
                                        ? formatDate(record.payment_date)
                                        : "-"
                                }}
                            </template>
                            <template v-if="column.dataIndex == 'month'">
                                {{ getMonthNameByNumber(record.month) }}
                                {{ record.year }}
                            </template>
                            <template v-if="column.dataIndex == 'net_salary'">
                                {{ formatAmountCurrency(record.net_salary) }}
                            </template>
                            <template v-if="column.dataIndex == 'status'">
                                <div v-if="record.status == 'generated'">
                                    <a-tag color="yellow">
                                        {{ $t(`payroll.generated`) }}
                                    </a-tag>
                                </div>
                                <div v-if="record.status == 'paid'">
                                    <a-tag color="green">
                                        {{ $t(`common.paid`) }}
                                    </a-tag>
                                </div>
                            </template>
                            <template v-if="column.dataIndex === 'action'">
                                <a-space>
                                    <a-button
                                        type="primary"
                                        @click="() => editItem(record)"
                                        style="margin-left: 4px"
                                    >
                                        <template #icon><EyeOutlined /></template>
                                    </a-button>
                                </a-space>
                            </template>
                        </template>
                    </a-table>
                </div>
            </a-col>
        </a-row>
    </admin-page-table-content>
</template>
<script>
import { onMounted, ref, watch } from "vue";
import {
    PlusOutlined,
    EditOutlined,
    DeleteOutlined,
    EyeOutlined,
} from "@ant-design/icons-vue";
import fields from "./fields";
import hrmManagement from "../../../../../common/composable/hrmManagement";
import datatable from "../../../../../common/composable/datatable";
import common from "../../../../../common/composable/common";
import { useI18n } from "vue-i18n";
import AdminPageHeader from "../../../../../common/layouts/AdminPageHeader.vue";
import crud from "../../../../../common/composable/crud";
import AddEdit from "./AddEdit.vue";

export default {
    components: {
        PlusOutlined,
        EditOutlined,
        DeleteOutlined,
        EyeOutlined,

        AdminPageHeader,
        AddEdit,
    },
    setup() {
        const { t } = useI18n();
        const { url, columns, filterableColumns, hashableColumns, initData } = fields();
        const datatableVariables = datatable();
        const crudVariables = crud();
        const {
            permsArray,
            appSetting,
            formatAmountCurrency,
            formatDate,
            dayjs,
        } = common();
        const { getMonthNameByNumber, monthArrays } = hrmManagement();
        const extraFilters = ref({
            month: dayjs().format("MM"),
            year: dayjs(),
        });

        onMounted(() => {
            setUrlData();
        });

        const setUrlData = () => {
            crudVariables.initData.value = { ...initData };
            crudVariables.hashableColumns.value = [...hashableColumns];

            crudVariables.tableUrl.value = {
                extraFilters: {
                    month: extraFilters.value.month,
                    year: extraFilters.value.year.format("YYYY"),
                },
                url,
            };
            crudVariables.table.filterableColumns = filterableColumns;

            crudVariables.fetch({
                page: 1,
            });
        };

        return {
            ...crudVariables,
            columns,
            filterableColumns,
            permsArray,
            appSetting,
            formatDate,
            setUrlData,
            monthArrays,
            extraFilters,
            getMonthNameByNumber,
            formatAmountCurrency,
        };
    },
};
</script>
