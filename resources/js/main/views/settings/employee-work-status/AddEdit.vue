<template>
    <a-modal
        :open="visible"
        :closable="false"
        :centered="true"
        :title="pageTitle"
        @ok="onSubmit"
    >
        <a-form layout="vertical">
            <a-row :gutter="16">
                <a-col :xs="24" :sm="24" :md="24" :lg="24">
                    <a-form-item
                        :label="$t('employee_work_status.name')"
                        name="name"
                        :help="rules.name ? rules.name.message : null"
                        :validateStatus="rules.name ? 'error' : null"
                        class="required"
                    >
                        <a-select
                            v-model:value="formData.name"
                            :placeholder="
                                $t('common.select_default_text', [
                                    $t('employee_work_status.name'),
                                ])
                            "
                        >
                            <a-select-option value="fulltime">
                                {{ $t("employee_work_status.fulltime") }}
                            </a-select-option>
                            <a-select-option value="contract">
                                {{ $t("employee_work_status.contract") }}
                            </a-select-option>
                            <a-select-option value="probation">
                                {{ $t("employee_work_status.probation") }}
                            </a-select-option>
                            <a-select-option value="work_from_home">
                                {{ $t("employee_work_status.work_from_home") }}
                            </a-select-option>
                        </a-select>
                    </a-form-item>
                </a-col>
            </a-row>
        </a-form>
        <template #footer>
            <a-space>
                <a-button
                    key="submit"
                    type="primary"
                    :loading="loading"
                    @click="onSubmit"
                >
                    <template #icon>
                        <SaveOutlined />
                    </template>
                    {{
                        addEditType == "add"
                            ? $t("common.create")
                            : $t("common.update")
                    }}
                </a-button>
                <a-button key="back" @click="onClose">
                    {{ $t("common.cancel") }}
                </a-button>
            </a-space>
        </template>
    </a-modal>
</template>

<script>
import { defineComponent, ref, watch } from "vue";
import {
    PlusOutlined,
    LoadingOutlined,
    SaveOutlined,
} from "@ant-design/icons-vue";
import apiAdmin from "../../../../common/composable/apiAdmin";

export default defineComponent({
    props: [
        "data",
        "visible",
        "url",
        "addEditType",
        "pageTitle",
        "successMessage",
    ],
    components: {
        PlusOutlined,
        LoadingOutlined,
        SaveOutlined,
    },
    setup(props, { emit }) {
        const { addEditRequestAdmin, loading, rules } = apiAdmin();
        const formData = ref({});
        const disabled = ref(false);
        const monthlyToWeekly = (value) => value / 4.333;
        const monthlyToBiweekly = (value) => value / 2.165;
        const weeklyToMonthly = (value) => value * 4.333;
        const biweeklyToMonthly = (value) => value * 2.165;

        const onInputChange = (changedValue, changedInput) => {
            const value = parseFloat(changedValue) || 0;

            if (value === 0 || changedValue === "") {
                formData.value.monthly = 0;
                formData.value.semi_monthly = 0;
                formData.value.weekly = 0;
                formData.value.bi_weekly = 0;
                return;
            }

            if (changedInput === "monthly") {
                formData.value.monthly = value;
                formData.value.semi_monthly = value / 2;
                formData.value.weekly = parseFloat(
                    monthlyToWeekly(value).toFixed(2)
                );
                formData.value.bi_weekly = parseFloat(
                    monthlyToBiweekly(value).toFixed(2)
                );
            } else if (changedInput === "semi_monthly") {
                formData.value.semi_monthly = value;
                formData.value.monthly = value * 2;
                formData.value.weekly = parseFloat(
                    monthlyToWeekly(formData.value.monthly).toFixed(2)
                );
                formData.value.bi_weekly = parseFloat(
                    monthlyToBiweekly(formData.value.monthly).toFixed(2)
                );
            } else if (changedInput === "weekly") {
                formData.value.weekly = value;
                formData.value.monthly = parseFloat(
                    weeklyToMonthly(value).toFixed(2)
                );
                formData.value.semi_monthly = formData.value.monthly / 2;
                formData.value.bi_weekly = parseFloat(
                    monthlyToBiweekly(formData.value.monthly).toFixed(2)
                );
            } else if (changedInput === "bi_weekly") {
                formData.value.bi_weekly = value;
                formData.value.monthly = parseFloat(
                    biweeklyToMonthly(value).toFixed(2)
                );
                formData.value.semi_monthly = formData.value.monthly / 2;
                formData.value.weekly = parseFloat(
                    monthlyToWeekly(formData.value.monthly).toFixed(2)
                );
            }
        };

        const onSubmit = () => {
            var newFormData = {};

            if (props.addEditType == "add") {
                newFormData = { ...formData.value };
            } else {
                newFormData = { ...formData.value, _method: "put" };
            }
            addEditRequestAdmin({
                url: props.url,
                data: newFormData,
                successMessage: props.successMessage,
                success: (res) => {
                    emit("addEditSuccess", res.xid);
                },
            });
        };

        const onClose = () => {
            rules.value = {};
            emit("closed");
        };

        const disabledValueType = () => {
            axiosAdmin
                .get(`disabled-value/${props.data.xid}`)
                .then((response) => {
                    disabled.value = response.data.disabled;
                });
        };

        watch(
            () => props.visible,
            () => {
                disabled.value = false;
                if (props.addEditType == "add") {
                    formData.value = {};
                    formData.value.value_type = "fixed";
                } else if (props.addEditType == "edit") {
                    formData.value = { ...props.data };
                    disabledValueType();
                }
            }
        );

        return {
            loading,
            rules,
            onClose,
            onSubmit,
            formData,
            onInputChange,
            disabled,
            drawerWidth: window.innerWidth <= 991 ? "90%" : "45%",
        };
    },
});
</script>
