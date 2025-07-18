import common from "@/common/composable/common";
import { useI18n } from "vue-i18n";

const fields = () => {
    const { user } = common();
    const { t } = useI18n();
    const url =
        "users?fields=id,xid,user_type,name,email,employee_number,shft_id_list,x_shft_id_list,gender,sss_no,philhealth_no,pagibig_no,tin_no,sss_fixed,philhealth_fixed,pagibig_fixed,tin_fixed,allow_login,dob,joining_date,is_married,marriage_date,personal_email,personal_phone,report_to,x_report_to,is_manager,visibility,reporter{id,xid,name},location_id,x_location_id,location{id,xid,name},designation_id,x_designation_id,designation{id,xid,name},profile_image,profile_image_url,phone,address,status,created_at,role_id,x_role_id,role{id,xid,name,display_name},basic_salary,probation_start_date,probation_end_date,notice_start_date,notice_end_date,duration,shift_id,x_shift_id,shift{id,xid,name},department_id,x_department_id,department{id,xid,name},end_date,annual_ctc,divisor,calculation_type,semi_monthly_rate,hourly_rate,daily_rate,sss_no,salary_group_id,x_salary_group_id,salaryGroup{id,xid},employee_status_id,x_employee_status_id,employeeWorkStatus{id,xid,name},OverideShift{id,xid,date,schedule_type,time_in,time_out,x_shift_id},OverideShift:scheduleLocation{id,xid,name},OverideShift:shift{id,xid,name}}&sort=-created_at&hashable_columns=location_id,report_to,department_id,designation_id,salary_group_id,shift_id,employee_status_id,shft_id_list";
    const addEditUrl = "users";
    const hashableColumns = [
        "location_id",
        "report_to",
        "department_id",
        "designation_id",
        "salary_group_id",
        "shift_id",
        "employee_status_id",
        // "shft_id_list",
    ];

    const initData = {
        name: "",
        email: "",
        employee_number: "",
        gender: "female",
        allow_login: 1,
        is_manager: 0,
        dob: undefined,
        joining_date: undefined,
        is_married: undefined,
        marriage_date: undefined,
        personal_email: undefined,
        personal_phone: "",
        report_to: user.value.xid,
        password: "",
        profile_image: undefined,
        profile_image_url: undefined,
        phone: "",
        address: "",
        status: "active",
        user_type: "staff_members",
        location_id: user.value.x_location_id,
        shift_id: undefined,
        probation_start_date: undefined,
        probation_end_date: undefined,
        notice_start_date: undefined,
        notice_end_date: undefined,
        department_id: user.value.x_department_id,
        designation_id: undefined,
        salary_group_id: undefined,
        employee_status_id: undefined,
        sss_no: "",
        overide_shift: [],
        // shft_id_list: undefined,
        
    };

    const columns = [
        {
            title: t("user.name"),
            dataIndex: "name",
            key: "name",
        },
        {
            title: t("user.report_to"),
            dataIndex: "report_to",
            key: "report_to",
        },
        {
            title: t("user.working_email"),
            dataIndex: "email",
        },
        {
            title: t("user.department"),
            dataIndex: "department",
            key: "department",
        },
        {
            title: t("user.location_id"),
            dataIndex: "location_id",
            key: "location_id",
        },
        {
            title: t("user.duration"),
            dataIndex: "duration",
        },
        {
            title: t("user.status"),
            dataIndex: "status",
            key: "status",
        },
        {
            title: t("common.action"),
            dataIndex: "action",
        },
    ];

    const filterableColumns = [
        {
            key: "name",
            value: t("user.name"),
        },
        {
            key: "email",
            value: t("user.working_email"),
        },
        {
            key: "phone",
            value: t("user.working_phone"),
        },
    ];

    return {
        url,
        initData,
        columns,
        filterableColumns,
        addEditUrl,
        hashableColumns,
    };
};

export default fields;
