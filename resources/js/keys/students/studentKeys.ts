import type { ComputedRef, Ref } from "vue";
import type { Student } from "@/types/student/student";
import type { InjectionKey } from "vue";
export type StudentReasonKey =
        | 'leave_of_absence'
        | 'transfer'
        | 'withdrawal'
        | 'graduation'
        | 'delinquency'
        | 'administrative';


export interface StudentCreateModal {
        isOpen: Ref<boolean>;
}

export interface StudentEditModal {
        student: Ref<Student | null>;
}

export interface StudentDeleteModal {
        student: Ref<Student | null>;
}

export interface StudentDeactivateModal {
        student: Ref<Student | null>;
}

export interface StudentActivateModal {
        student: Ref<Student | null>;
}

export interface SelectedStudentKey {
        student: Ref<Student | null>;
}

export interface StudentsResponse {
        students: Ref<Student[] | []>;
}

export interface StudentsGroup {
        students: Ref<Student[] | []>;
}

export type RefreshTableFn = () => void;

export interface StudentTabContext {
    student: ComputedRef<Student>;
    editModalOpen: Ref<boolean>;
    academicDataEditModalOpen: Ref<boolean>;
}

export const StudentTabContextKey: InjectionKey<StudentTabContext> = Symbol('StudentTabContextKey');

export const StudentCreateKey: InjectionKey<StudentCreateModal> = Symbol("StudentCreateKey");
export const StudentEditKey: InjectionKey<StudentEditModal> = Symbol("StudentEditKey");
export const StudentDeleteKey: InjectionKey<StudentDeleteModal> = Symbol("StudentDeleteKey");
export const StudentDeactivateKey: InjectionKey<StudentDeactivateModal> = Symbol("StudentDeactivateKey");
export const StudentActivateKey: InjectionKey<StudentActivateModal> = Symbol("StudentActivateKey");
export const SelectedStudentKey: InjectionKey<Ref<SelectedStudentKey>> = Symbol('SelectedStudentKey');
export const StudentsResponse: InjectionKey<Ref<StudentsResponse>> = Symbol('StudentsResponse');
export const StudentsGroupKey: InjectionKey<Ref<StudentsGroup>> = Symbol('StudentsGroupKey');
export const RefreshTableKey: InjectionKey<Ref<RefreshTableFn | null>> = Symbol('RefreshTableKey');